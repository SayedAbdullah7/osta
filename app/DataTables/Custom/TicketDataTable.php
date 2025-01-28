<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\Ticket;

class TicketDataTable extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array $searchableRelations = [
//        'user' => ['name'], // Searching by one column in service
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
            Column::create('title'),
            Column::create('description'),
            Column::create('status'),
            Column::create('user_type'),
            Column::create('user_id'),
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
            'status' => Filter::select('select status', ['open' => 'Open', 'closed' => 'Closed', 'pending' => 'Pending']),
            'user_type' => Filter::select('user type', [User::class => 'User', Provider::class => 'Ticket']),

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
        $query = Ticket::query()->with('user');

        return DataTables::of($query)
            ->editColumn('user_id', fn ($model) => $model->user?->short_name)
            ->editColumn('user_type', fn ($model) => class_basename($model->user_type))
            ->editColumn('description', fn ($model) => Str::limit($model->description, 35))
            ->editColumn('name', fn ($model) => Str::limit($model->name, 20))
            ->editColumn('status', fn ($model) => $this->formatStatusBadge($model->status))
            ->editColumn('created_at', fn ($model) =>  $model->created_at ? \Carbon\Carbon::parse($model->created_at)->format('Y-m-d H:i') : '-')
            ->editColumn('updated_at', fn ($model) =>  $model->updated_at ? \Carbon\Carbon::parse($model->updated_at)->format('Y-m-d H:i') : '-')
            ->addColumn('action', function ($model) {
                return view('pages.conversation.columns._actions', compact('model'));
            })
            ->rawColumns(['status', 'action'])
            ->filter(fn ($query) => $this->applySearch($query),true)
            ->make(true);
    }

    private function formatStatusBadge($status): string
    {
        $class = $this->statusClass($status);
        return "<span class='p-2 text-capitalize text-white badge text-{$class} bg-light-{$class}'>{$status}</span>";
    }

    /**
     * Get CSS class for status badges.
     *
     * @param string $status
     * @return string
     */
    private function statusClass(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'closed' => 'success',
             'open' => 'danger',
            default => 'dark',
        };
    }
}
