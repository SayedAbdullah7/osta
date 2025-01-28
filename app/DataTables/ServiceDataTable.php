<?php

namespace App\DataTables;

use App\Models\Service;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\App;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ServiceDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('area', function (Service $area) {
                return view('pages.area.columns._user', compact('area'));
            })
//            ->editColumn('name', function (Service $area) {
//                return ucwords($area->name);
//            })
//            ->editColumn('last_login_at', function (Service $area) {
//                return sprintf('<div class="badge badge-light fw-bold">%s</div>', $area->last_login_at ? $area->last_login_at->diffForHumans() : $area->updated_at->diffForHumans());
//            })
            ->editColumn('created_at', function (Service $area) {
                return $area->created_at->format('d M Y, h:i a');
            })
            ->addColumn('action', function (Service $service) {
                return view('pages.service.columns._actions', compact('service'));
            })
            ->setRowId('id');
//            ->addColumn('action', 'area.action')
//            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Service $model): QueryBuilder
    {
        return $model->newQuery()->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('areas-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(2);
//            ->drawCallback("function() {" . file_get_contents(resource_path('views/pages//apps/area-management/areas/columns/_draw-scripts.js')) . "}");
        return $this->builder()
            ->setTableId('areas-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->addClass('d-flex align-items-center')->name('name'),
            Column::make('name')->searchable(false),
//            Column::make('last_login_at')->title('Last Login'),
            Column::make('created_at')->title('Joined Date')->addClass('text-nowrap'),
            Column::computed('action')
                ->addClass('text-end text-nowrap')
                ->exportable(false)
                ->printable(false)
                ->width(60)
//            Column::computed('action')
//                  ->exportable(false)
//                  ->printable(false)
//                  ->width(60)
//                  ->addClass('text-center'),
//            Column::make('id'),
//            Column::make('name'),
//            Column::make('created_at'),
//            Column::make('updated_at'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Services_' . date('YmdHis');
    }
}
