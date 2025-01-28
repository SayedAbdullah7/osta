<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Warranty;

class WarrantyDataTable extends BaseDataTable
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
            Column::create('description'),
            Column::create('duration_months'),
            Column::create('percentage_cost'),
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
        $query = Warranty::query();

        return DataTables::of($query)
            ->editColumn('description', fn ($order) => Str::limit($order->description, 25))
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
            ->addColumn('action', function ($model) {
                return view('pages.warranty.columns._actions', compact('model'));
            })
            ->filter(fn ($query) => $this->applySearch($query), true)
            ->make(true);
    }
}
