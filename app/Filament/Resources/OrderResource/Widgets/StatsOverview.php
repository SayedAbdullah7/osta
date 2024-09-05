<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Filament\Resources\OrderResource;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageTable;
//    protected static bool $isDiscovered = false;
    protected function getColumns(): int
    {
        return 2;
    }

    protected function getTablePage(): string
    {
        return OrderResource\Pages\ListOrders::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Orders Count', $this->getPageTableQuery()->count())
                ->description('total number of orders')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('success')
                ->chart([1, 2, 3, 4, 5, 5, 6]),
            Stat::make('Total price',  $this->getPageTableQuery()->sum('price')),
        ];
    }
}
