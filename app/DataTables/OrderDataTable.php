<?php

namespace App\DataTables;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OrderDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable(QueryBuilder $query): \Yajra\DataTables\DataTableAbstract
    {
        return datatables()
            ->of($query)
//            ->addColumn('action', function ($model) {
//                return view('pages.order.columns._actions', compact('model'));
//            })
            ->editColumn('status', function ($order) {
                return "<span class='text-white text-capitalize badge bg-" . $this->statusClass($order->statusText()) . "'>{$order->statusText()}</span>";
            })
            ->editColumn('is_confirmed', function ($order) {
                return $order->is_confirmed ? 'Yes' : 'No';
            })
            ->editColumn('start', function ($order) {
                return $order->start ? \Carbon\Carbon::parse($order->start)->format('Y-m-d H:i') : '-';
            })
            ->editColumn('end', function ($order) {
                return $order->end ? \Carbon\Carbon::parse($order->end)->format('Y-m-d H:i') : '-';
            })
            ->editColumn('service_id', function ($order) {
                return $order?->service?->name;
            })
            ->editColumn('user_id', function ($order) {
                return $order?->user?->definition(); // Assuming a relationship with the User model
            })
            ->editColumn('provider_id', function ($order) {
                return $order?->provider?->definition(); // Assuming a relationship with the User model
            })
            ->editColumn('price', function ($order) {
                return $order->price ? '$' . number_format($order->price, 2) : '-';
            })
//            ->editColumn('location', function ($order) {
//                return  "Lat: {$order->location_latitude}, Long: {$order->location_longitude}";
//                return $order->location_desc ?: "Lat: {$order->location_latitude}, Long: {$order->location_longitude}";
//            })
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('Y-m-d H:i:s'); // Adjust format as needed
            })
            ->rawColumns(['status', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Order $model
     * @return QueryBuilder
     */
    public function query(Order $model): QueryBuilder
    {
        return $model->newQuery()->with(['user']); // Add relationships if needed
    }

    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('id')->title(' ID'),
//            Column::make('start')->title('Start Time'),
//            Column::make('end')->title('End Time'),
            Column::make('status')->title('Status'),
            Column::make('service_id')->title('Service'),
            Column::make('max_allowed_price')->title('Max Allowed Price'),
            Column::make('price')->title('Price'),
            Column::make('is_confirmed')->title('Confirmed'),
            Column::make('category')->title('Category'),
            Column::make('price')->title('Price'),
            Column::make('user_id')->title('User')->searchable(false),
            Column::make('provider_id')->title('Provider')->searchable(false),
            Column::make('created_at'),

//            Column::make('location')->title('Location')->searchable(false),
//            Column::computed('action')
//                ->exportable(false)
//                ->printable(false)
//                ->width(120)
//                ->addClass('text-center')
//                ->title('Actions'),
        ];
    }

    /**
     * Build the HTML for the DataTable.
     *
     * @param HtmlBuilder $htmlBuilder
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('orders-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
            ->orderBy(0, 'desc')
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Orders_' . date('YmdHis');
    }

    /**
     * Get CSS class for status badges.
     *
     * @param string $status
     * @return string
     */
    private function statusClass(string $status): string
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
