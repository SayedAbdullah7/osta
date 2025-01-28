<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Setting;

class SettingDataTable extends BaseDataTable
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
            Column::create('key'),
            Column::create('value'),
//            Column::create('created_at'),
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
            'updated_at' => Filter::date('Updated Date','now'),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = Setting::query();

        return DataTables::of($query)
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->addColumn('action', function ($model) {
                return view('pages.setting.columns._actions', compact('model'));
            })
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
            ->rawColumns([ 'action'])
            ->make(true);
    }
}
