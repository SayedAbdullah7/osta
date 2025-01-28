<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\ContractorRequest;

class ContractorRequestDataTable extends BaseDataTable
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
            Column::create('description'),
            Column::create('space'),
            Column::create('user_id'),
            Column::create('created_at'),
//            Column::create('updated_at'),
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
        $query = ContractorRequest::query();

        return DataTables::of($query)
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->editColumn('user_id', fn ($row) => $row->user?->name)
            ->editColumn('created_at', fn ($row) => $row->created_at?->format('Y-m-d'))
            ->make(true);
    }
}
