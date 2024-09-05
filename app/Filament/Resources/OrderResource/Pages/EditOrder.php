<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\ProviderOrderService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Resolve the service using Laravel's service container
        $providerOrderService = app(ProviderOrderService::class);

        DB::transaction(function () use ($data, $record, $providerOrderService) {
            $newPrice = $data['price'];
            $oldPrice = $record->price;
            if ($newPrice != $oldPrice) {
                $providerOrderService->updateOrderPrice($record, $newPrice);
            }
            $record->update($data);
        });

        return $record;
    }

//    protected function getSavedNotification(): ?Notification
//    {
//        return Notification::make()
//            ->success()
//            ->title('User updated')
//            ->body('The user has been saved successfully.');
//    }
}
