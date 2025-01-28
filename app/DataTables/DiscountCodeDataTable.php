<?php

namespace App\DataTables;

use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DiscountCodeDataTable extends DataTable
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
            ->addColumn('action', function ($discountCode) {
                // You can customize the action view or logic here
                return view('pages.discount-code.columns._actions', compact('discountCode'));
            })
            ->editColumn('expires_at', function ($discountCode) {
                return \Carbon\Carbon::parse($discountCode->expires_at)->format('Y-m-d');
                return $discountCode->expires_at instanceof \Carbon\Carbon
                    ? $discountCode->expires_at->format('Y-m-d H:i:s')
                    : '-'; // Return '-' if expires_at is not a valid Carbon instance
            })
            ->editColumn('used_at', function ($discountCode) {
                return \Carbon\Carbon::parse($discountCode->used_at)->format('Y-m-d H:i:s');
                return $discountCode->used_at instanceof \Carbon\Carbon
                    ? $discountCode->used_at->format('Y-m-d H:i:s')
                    : '-'; // Return '-' if used_at is not a valid Carbon instance
            })
            ->editColumn('is_active', function ($discountCode) {
                return $discountCode->is_active ? 'Active' : 'Inactive'; // Show Active/Inactive based on the value
            })
            ->editColumn('used_by', function ($discountCode) {
                return $discountCode->used_by->name ?? '-'; // Return '-' if used_by is null
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param DiscountCode $model
     * @return QueryBuilder
     */
    public function query(DiscountCode $model): QueryBuilder
    {
        return $model->newQuery(); // You can add any relationships if needed
    }

    /**
     * Optional method for modifying the format of the data before it is sent to the client.
     *
     * @param mixed $row
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('code'),
            Column::make('value'),
            Column::make('type'),
            Column::make('is_active')->title('Status'),
            Column::make('expires_at')->title('Expires At'),
            Column::make('used_at')->title('Used At'),
            Column::make('used_by')->title('Used By'), // You may want to join a users table if you want user details
            Column::computed('action')
                ->addClass('text-end text-nowrap')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->title('Actions'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'DiscountCodes_' . date('YmdHis');
    }

    /**
     * Build the HTML for the DataTable.
     *
     * @param HtmlBuilder $htmlBuilder
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('discount-codes-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'desc');
    }
}
