<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Provider;

class ProviderDataTable extends BaseDataTable
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
            Column::create('last_name'),
            Column::create('phone'),
//            Column::create('is_phone_verified'),
            Column::create('is_approved')->setTitle('Approved'),
            Column::create('email'),
//            Column::create('email_verified_at'),
            Column::create('gender'),
            Column::create('country_id')->setTitle('Country'),
            Column::create('city_id')->setTitle('City'),
//            Column::create('remember_token'),
            Column::create('created_at'),
            Column::create('updated_at'),
            Column::create('action'),
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
        $query = Provider::query();

        return DataTables::of($query)
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
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
            ->rawColumns(['action','is_phone_verified', 'is_approved'])
//            ->filter(fn ($query) => $this->applySearch($query))
            ->make(true);
    }
}
