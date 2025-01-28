<?php

namespace App\DataTables\Custom;

use App\Enums\OrderStatusEnum;
use App\Helpers\Column;
use App\Helpers\Filter;
use App\Models\Order;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class OrderDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
        'service' => ['name'], // Searching by one column in service
        'user' => ['name'], // Searching by one column in user
        'provider' => ['first_name', 'last_name'], // Searching by first_name and last_name in provider
    ];


    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::create('id')
                ->setSearchable(true)
                ->setOrderable(true),

//            Column::create('space')
//                ->setTitle('Space'),

//            Column::create('warranty_id')
//                ->setTitle('Warranty ID'),

            Column::create('status')->setTitle('Order Status'),
            Column::create('category'),
            Column::create('desc'),
            Column::create('max_allowed_price'),
            Column::create('discount_code'),
            Column::create('offer_count'),
            Column::create('user_id'),
            Column::create('service_id'),
            Column::create('provider_id'),

            Column::create('is_confirmed')
                ->setTitle('Confirmed')
                ->setSearchable(false),

            Column::create('price')
                ->setTitle('Price'),

            Column::create('created_at')
                ->setTitle('Created Date')
                ->setOrderable(true),
            Column::create('action')->setOrderable(false)
        ];
    }

    /**
     * Get the filters for the DataTable.
     *
     * @return array
     */
    public function filters()
    {
        return [
            'status' => Filter::select('select status', OrderStatusEnum::toArray()),
            'created_at' => Filter::date('Created Date', '2024-01-08', '2021-12-31'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = Order::query();

        return DataTables::of($query)
            ->editColumn('status', fn ($order) => self::formatStatusBadge($order))
            ->editColumn('is_confirmed', fn ($order) => $order->is_confirmed ? 'Yes' : 'No')
            ->editColumn('price', fn ($order) => $this->formatCurrency($order->price))
            ->editColumn('desc', fn ($order) => Str::limit($order->desc, 20))
            ->editColumn('service_id', fn ($order) => $order->service?->name)
            ->editColumn('user_id', fn ($order) => $order->user?->short_name)
            ->editColumn('provider_id', fn ($order) => $order->provider?->short_name)
            ->editColumn('created_at', fn ($order) => $this->formatDate($order->created_at))
//            {{addColumns}}
//            ->filter(function ($query) {
////                {{filterConditions}}
//            })
//            ->rawColumns(['actions'])
//            ->filter(function ($query) {
//                foreach (request()->only(array_keys($this->filters())) as $key => $value) {
//                    if ($value) {
//                        $query->where($key, $value);
//                    }
//                }
//            })
//            ->filter(function ($query) {
//                $request = request();
//                if ($request->has('search') && !empty($request->search['value'])) {
//                    $searchTerm = $request->search['value'];
//                    foreach ($this->searchableRelations as $relation => $column) {
//                        $query->orWhereHas($relation, function($q) use ($column, $searchTerm) {
//                            $q->where($column, 'like', '%' . $searchTerm . '%');
//                        });
//                    }
//                }
//            })
            ->editColumn('action', function ($model) {
                return view('pages.order.columns._actions', compact('model'));
            })
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public static function formatStatusBadge($order): string
    {
        $class = self::statusClass($order->statusText());
        return "<span class='text-capitalize text-white badge bg-{$class}'>{$order->statusText()}</span>";
    }

    private function formatCurrency(?float $amount): string
    {
        return $amount ? '$' . number_format($amount, 2) : '-';
    }

    private function formatDate(?string $date): string
    {
        return $date ? \Carbon\Carbon::parse($date)->format('Y-m-d H:i') : '-';
    }

    /**
     * Get CSS class for status badges.
     *
     * @param string $status
     * @return string
     */
    public static function statusClass(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'accepted' => 'primary',
            'coming' => 'info',
            'almost done' => 'secondary',
            'done' => 'success',
            'rejected', 'canceled' => 'danger',
            default => 'light',
        };
    }
}
