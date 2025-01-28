<?php

namespace App\DataTables;

use App\Models\SpaceSubService;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;


class SpaceSubServiceDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query
     * @return EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return datatables()
            ->of($query)
            ->addColumn('action', function ($spaceSubService) {
                return view('pages.space.columns._actions', compact('spaceSubService'));
            })
//            ->editColumn('max_price', function ($spaceSubService) {
//                return number_format($spaceSubService->max_price, 2); // Format max price
//            })
            ->editColumn('space_id', function ($spaceSubService) {
                return $spaceSubService->space->name; // Assuming the Space model has a 'name' attribute
            })
            ->editColumn('sub_service_id', function ($spaceSubService) {
                return $spaceSubService->subService->name; // Assuming the SubService model has a 'name' attribute
            });

    }

    /**
     * Get query source of dataTable.
     *
     * @param SpaceSubService $model
     * @return QueryBuilder
     */
    public function query(SpaceSubService $model)
    {
        return $model->newQuery()->with(['space', 'subService']); // Eager load related models
    }

    /**
     * Get columns for the DataTable.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('space_id')->title('Space'),
            Column::make('sub_service_id')->title('Sub Service'),
            Column::make('max_price'),
            Column::computed('action')
                ->addClass('text-end text-nowrap')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->title('Actions'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'SpaceSubServices_' . date('YmdHis');
    }

    /**
     * Build the HTML for the DataTable.
     *
     * @param HtmlBuilder $htmlBuilder
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('subservices-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'desc');
    }
}
