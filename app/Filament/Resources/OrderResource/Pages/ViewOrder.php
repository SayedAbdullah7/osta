<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
    protected function getColumns(): array
    {
        return [
            // other columns...

            Tables\Columns\TextColumn::make('sub_services')
                ->label('Sub Services')
                ->formatStateUsing(function ($record) {
                    return $record->subServices->map(function ($subService) {
                        return "{$subService->name} (Quantity: {$subService->pivot->quantity})";
                    })->implode(', ');
                }),
        ];
    }
}
