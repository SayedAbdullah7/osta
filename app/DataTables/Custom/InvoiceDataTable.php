<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use App\Models\Transaction;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Invoice;

class InvoiceDataTable extends BaseDataTable
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
            Column::create('uuid'),
//            Column::create('invoice_number'),
//            Column::create('status'),
//            Column::create('cost'),
            Column::create('discount'),
            Column::create('tax'),
            Column::create('sub_total'),
            Column::create('total'),
            Column::create('paid'),
            Column::create('provider_earning'),
            Column::create('admin_earning'),
//            Column::create('details'),
//            Column::create('payment_method'),
            Column::create('payment_status'),
//            Column::create('payment_id'),
//            Column::create('payment_url'),
            Column::create('user_id'),
            Column::create('order_id'),
            Column::create('created_at'),
            Column::create('updated_at'),
            Column::create('action')->setSearchable(false)->setOrderable(false),
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
    public function handle($orderId = null)
    {
        $query = Invoice::query();
        if ($orderId) {
            $query->where('order_id', $orderId);
        }
        return DataTables::of($query)
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
           ->addColumn('action', function ($model) {
                return view('pages.invoice.columns._actions', compact('model'));
            })
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->make(true);
    }
}
