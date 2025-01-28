<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use App\Models\Admin;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Wallet;

class WalletDataTable extends BaseDataTable
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
            Column::create('holder_type')->setTitle('type'),
            Column::create('holder_id')->setTitle('user'),
//            Column::create('holder_id'),
//            Column::create('name'),
//            Column::create('slug'),
//            Column::create('uuid'),
//            Column::create('description'),
//            Column::create('meta'),
            Column::create('balance'),
//            Column::create('decimal_places'),
//            Column::create('created_at'),
            Column::create('updated_at')->setTitle('last transaction'),
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
//            'created_at' => Filter::date('Created Date','now'),
            'holder_type' => Filter::select('select user type', [User::class => 'User', Provider::class => 'Provider',Admin::class => 'Admin']),

        ];
    }

    /**
     * Handle the DataTable data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle()
    {
        $query = Wallet::query();

        return DataTables::of($query)
            ->editColumn('holder_id', fn ($model) => $model->holder?->short_name)
            ->editColumn('holder_type', fn ($model) => class_basename($model->holder_type))
//            ->editColumn('desc', fn ($model) => Str::limit($model->desc, 20))
//            ->editColumn('desc', fn ($model) => Str::limit($model->desc, 20))
//            ->editColumn('name', fn ($model) => Str::limit($model->name, 20))
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
            ->addColumn('action', function ($model) {
                return view('pages.wallet.columns._actions', compact('model'));
            })
            ->rawColumns([ 'action'])

//            ->filter(fn ($query) => $this->applySearch($query),true)
            ->make(true);
    }
}
