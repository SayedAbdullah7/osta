<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
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
                // You can customize the action view or logic here
                return view('pages.user.columns._actions', compact('model'));
            })
            ->editColumn('is_phone_verified', function ($user) {
                return $user->is_phone_verified ? 'Verified' : 'Not Verified';
            })
            ->editColumn('gender', function ($user) {
                return $user->gender ? 'Male' : 'Female'; // Assuming 1 for Male, 0 for Female
            })
            ->editColumn('date_of_birth', function ($user) {
                return $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('Y-m-d') : '-';
            })
            ->editColumn('email_verified_at', function ($user) {
                return $user->email_verified_at ? \Carbon\Carbon::parse($user->email_verified_at)->format('Y-m-d H:i:s') : '-';
            })
            ->editColumn('orders', function ($user) {
                // If user has orders, return a badge
                return $user->hasActiveOrders()
                    ? '<span class="badge bg-primary text-white">Has Orders</span>'
                    : '<span class="badge bg-warning text-dark">No Orders</span>';
            })
            ->editColumn('country', function ($user) {
//                return '-';
                return $user->country ? $user->country->name : '-'; // Assuming 'country' is a relationship
            })
            ->rawColumns([ 'orders']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param User $model
     * @return QueryBuilder
     */
    public function query(User $model): QueryBuilder
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
            Column::make('name'),
            Column::make('phone'),
            Column::make('is_phone_verified')->title('Phone Verified'),
            Column::make('email'),
            Column::make('email_verified_at')->title('Email Verified At'),
            Column::make('gender'),
            Column::make('date_of_birth')->title('Date of Birth'),
            Column::make('country')->title('Country')->searchable(false), // Assuming 'country' is a relationship
            Column::make('orders')->title('Orders')->searchable(false), // Display the badge based on orders
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
        return 'Users_' . date('YmdHis');
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
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'desc');
    }
}
