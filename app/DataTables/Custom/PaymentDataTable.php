<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Payment;

class PaymentDataTable extends BaseDataTable
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
            Column::create('invoice_id'),
            Column::create('amount'),
            Column::create('payment_method'),
            Column::create('meta')->setTitle('description'),
//            Column::create('is_reviewed'),
            Column::create('creator_id'),
//            Column::create('reviewer_id'),
            Column::create('created_at'),
            Column::create('updated_at'),
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
        $query = Payment::query();

        return DataTables::of($query)
            ->editColumn('creator_id', fn ($model) => $model->creator?->short_name)
            ->editColumn('meta', fn ($model) => isset($model->meta['description']) ? $model->meta['description'] : '-')
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->make(true);
    }
}
