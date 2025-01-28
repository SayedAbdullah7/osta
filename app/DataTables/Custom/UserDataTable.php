<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\User;

class UserDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
            //
    ];

    /**
     * Get the columns for the DataTable.
     *
     * @return array
     */
    public function columns(): array
    {
        return [
            Column::create('id'),
            Column::create('name'),
            Column::create('phone'),
            Column::create('is_phone_verified'),
            Column::create('email'),
//            Column::create('email_verified_at'),
            Column::create('gender'),
            Column::create('date_of_birth'),
//            Column::create('remember_token'),
            Column::create('country_id'),
            Column::create('created_at'),
            Column::create('updated_at'),
            Column::create('action')
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
            'created_at' => Filter::date('Created Date','now'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = User::query();

        return DataTables::of($query)
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
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
            ->rawColumns([ 'orders'])
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->make(true);
    }
}
