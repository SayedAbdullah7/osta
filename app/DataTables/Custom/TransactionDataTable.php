<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use App\Models\Admin;
use App\Models\Provider;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Transaction;

class TransactionDataTable extends BaseDataTable
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
            Column::create('payable_type')->setTitle('type'),
            Column::create('payable_id')->setTitle('user'),
//            Column::create('payable_id'),
            Column::create('wallet_id'),
            Column::create('type'),
            Column::create('amount'),
//            Column::create('confirmed'),
            Column::create('meta')->setTitle('description'),
//            Column::create('uuid'),
            Column::create('created_at'),
//            Column::create('updated_at'),
//            Column::create('action'),
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
            'payable_type' => Filter::select('select user type', [User::class => 'User', Provider::class => 'Provider',Admin::class => 'Admin']),
        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle($walletId = null)
    {
        $query = Transaction::query();
        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }
        return DataTables::of($query)
            ->editColumn('payable_id', fn ($model) => $model->payable?->short_name)
            ->editColumn('payable_type', fn ($model) => class_basename($model->payable_type))
            ->editColumn('meta', fn ($model) => isset($model->meta['description']) ? $model->meta['description'] : '-')
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
//            ->addColumn('action', function ($model) {
//                return view('pages.transaction.columns._actions', compact('model'));
//            })
//            ->rawColumns([ 'action'])
//            ->filter(fn ($query) => $this->applySearch($query),true)
            ->make(true);
    }
}
