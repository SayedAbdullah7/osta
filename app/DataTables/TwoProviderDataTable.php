<?php

namespace App\DataTables;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TwoProviderDataTable extends DataTable
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
            ->addColumn('action', function ($model) {
                return view('pages.provider.columns._actions', compact('model'));
            })
//            ->editColumn('is_phone_verified', function ($provider) {
//                return $provider->is_phone_verified
//                    ? '<i class="fa-solid fa-check-circle text-success fs-1"></i>'
//                    : '<i class=" fa-solid fa-times-circle text-danger fs-1"></i>';            })
            ->editColumn('is_approved', function ($provider) {
                return $provider->is_approved
                    ? '<i class="fa-solid fa-check-circle text-success fs-1"></i>'
                    : '<i class=" fa-solid fa-times-circle text-danger fs-1"></i>';
            })
            ->editColumn('gender', function ($provider) {
                return $provider->gender ? 'Male' : 'Female'; // Assuming 1 for Male, 0 for Female
            })
            ->editColumn('country', function ($provider) {
                return $provider->country ? $provider->country->name : '-';
            })
            ->editColumn('city', function ($provider) {
                return $provider->city ? $provider->city->name : '-';
            })
            ->rawColumns(['action','is_phone_verified', 'is_approved']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Provider $model
     * @return QueryBuilder
     */
    public function query(Provider $model): QueryBuilder
    {
        return $model->newQuery()->with(['country', 'city']);
    }

    /**
     * Optional method for modifying the format of the data before it is sent to the client.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('first_name')->title('First Name'),
            Column::make('last_name')->title('Last Name'),
            Column::make('phone')->title('Phone'),
//            Column::make('is_phone_verified')->title('Phone Verified'),
            Column::make('email')->title('Email'),
            Column::make('is_approved')->title('Approved'),
            Column::make('gender')->title('Gender'),
            Column::make('country')->title('Country')->searchable(false),
            Column::make('city')->title('City')->searchable(false),
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
        return 'Providers_' . date('YmdHis');
    }

    /**
     * Build the HTML for the DataTable.
     *
     * @return HtmlBuilder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                ->setTableId('providers-table')
                ->columns($this->getColumns())
                ->minifiedAjax()
                ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>")
                ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
                ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->parameters()
                ->orderBy(0, 'desc');
        }
}
