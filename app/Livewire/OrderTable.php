<?php

namespace App\Livewire;

use App\Models\Service;
use Carbon\Carbon;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Order;
use Rappasoft\LaravelLivewireTables\Views\Filter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\BooleanFilter;

class OrderTable extends DataTableComponent
{
    protected $model = Order::class;

    public function query()
    {
        return Order::query()->with('user', 'provider', 'service');
    }

    public function columns(): array
    {
        return [
            Column::make('ID', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Space', 'space')
                ->sortable()
                ->searchable(),

            Column::make('Warranty', 'warranty_id')
                ->sortable(),

            Column::make('Status', 'status')
                ->sortable()
                ->searchable()
                ->format(fn($value) => $this->renderStatusBadge($value->value))
                ->html(),

            Column::make('Confirmed', 'is_confirmed')
                ->sortable()
                ->format(fn($value) => $value ? 'Yes' : 'No'),

            Column::make('Category', 'category')
                ->sortable()
                ->searchable(),

            Column::make('Price', 'price')
                ->sortable(),

            Column::make('Discount Code', 'discount_code')
                ->searchable(),

            Column::make('Offer Count', 'offer_count')
                ->sortable(),

            Column::make('Created At', 'created_at')
                ->sortable()
                ->format(fn($value) => $value ? $value->format('Y-m-d H:i') : 'N/A'),

            Column::make('Actions', 'id')
                ->format(fn($value, $row) => view('pages.order.columns._actions', ['model' => $row]))
                ->html(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('Status')
                ->options([
                    '' => 'Any',
                    'pending' => 'Pending',
                    'accepted' => 'Accepted',
                    'coming' => 'Coming',
                    'almost done' => 'Almost Done',
                    'done' => 'Done',
                    'rejected' => 'Rejected',
                    'canceled' => 'Canceled',
                ])
                ->filter(function ($query, $value) {
                    if ($value !== '') {
                        $query->where('status', $value);
                    }
                }),

            SelectFilter::make('Category')
                ->options([
                    '' => 'Any',
                    'basic' => 'Basic',
                    'space_based' => 'Space Based',
                    'technical' => 'Technical',
                    'other' => 'Other',
                ])
                ->filter(function ($query, $value) {
                    if ($value !== '') {
                        $query->where('category', $value);
                    }
                }),

//            BooleanFilter::make('Confirmed')
//                ->filter(function ($query, $value) {
//                    $query->where('is_confirmed', $value);
//                }),
//
//            BooleanFilter::make('Has Discount')
//                ->filter(function ($query, $value) {
//                    if ($value) {
//                        $query->whereNotNull('discount_code');
//                    } else {
//                        $query->whereNull('discount_code');
//                    }
//                }),

            // Service filter (Select)
            SelectFilter::make('Service')
                ->options(
                    Service::pluck('name', 'id')->toArray() // Assuming 'name' is the field to display
                )
                ->filter(function ($query, $value) {
                    if ($value) {
                        $query->where('service_id', $value);
                    }
                }),

            // Date filter for Created At
            SelectFilter::make('Created At')
                ->options([
                    '' => 'Any',
                    'last_7_days' => 'Last 7 Days',
                    'last_30_days' => 'Last 30 Days',
                    'last_90_days' => 'Last 90 Days',
                ])
                ->filter(function ($query, $value) {
                    if ($value === 'last_7_days') {
                        $query->where('created_at', '>=', Carbon::now()->subDays(7));
                    } elseif ($value === 'last_30_days') {
                        $query->where('created_at', '>=', Carbon::now()->subDays(30));
                    } elseif ($value === 'last_90_days') {
                        $query->where('created_at', '>=', Carbon::now()->subDays(90));
                    }
                }),


        ];
    }

    public function actions(): array
    {
        return [
            'view' => fn($row) => route('orders.show', $row->id),
            'edit' => fn($row) => route('orders.edit', $row->id),
        ];
    }

    /**
     * Define table attributes for styling.
     */
    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setTableAttributes([
                'class' => 'table table-striped table-hover align-middle text-gray-700',
            ])
            ->setThAttributes(function (Column $column) {
                if ($column->isField('actions')) {
                    return [
                        'class' => 'text-nowrap',
                    ];
                }
                return [
                    'class' => 'text-start text-muted fw-bold text-uppercase',
                ];
            })
            ->setTrAttributes(function ($row) {
                return match ($row->status) {
                    'done' => ['class' => 'table-success'],
                    'pending' => ['class' => 'table-warning'],
                    'rejected', 'canceled' => ['class' => 'table-danger'],
                    default => ['class' => 'text-nowrap'],
                };
            });
    }

    /**
     * Render status badges with dynamic classes.
     */
    private function renderStatusBadge(string $status): string
    {
        $classes = match ($status) {
            'pending' => 'badge bg-warning text-white',
            'accepted' => 'badge bg-primary  text-white',
            'coming' => 'badge bg-info',
            'almost done' => 'badge bg-secondary',
            'done' => 'badge bg-success  text-white',
            'rejected', 'canceled' => 'badge bg-danger  text-white',
            default => 'badge bg-light text-dark',
        };

        return "<span class='$classes'>" . ucfirst($status) . "</span>";
    }
}
