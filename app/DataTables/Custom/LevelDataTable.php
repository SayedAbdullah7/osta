<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Level;

class LevelDataTable extends BaseDataTable
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
            Column::create('level')->setTitle('Level number'),
            Column::create('name'),
            Column::create('percentage'),
            Column::create('orders_required'),
            Column::create('next_level_id'),
            Column::create('created_at'),
//            Column::create('updated_at'),
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
        $query = Level::query();

        return DataTables::of($query)
            ->editColumn('action', function ($model) {
                return view('pages.level.columns._actions', compact('model'));
            })
            ->editColumn('next_level_id', function ($model) {
                return $model->nextLevel ? $model->nextLevel->name : '-';
            })
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->make(true);
    }
}
