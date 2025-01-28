<?php

namespace App\DataTables;

use App\Models\Area;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AreasDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('area', function (Area $area) {
                return view('pages.area.columns._user', compact('area'));
            })
            ->editColumn('name', function (Area $area) {
                return ucwords($area->name);
            })
//            ->editColumn('last_login_at', function (Area $area) {
//                return sprintf('<div class="badge badge-light fw-bold">%s</div>', $area->last_login_at ? $area->last_login_at->diffForHumans() : $area->updated_at->diffForHumans());
//            })
            ->editColumn('created_at', function (Area $area) {
                return $area->created_at->format('d M Y, h:i a');
            })
            ->addColumn('action', function (Area $area) {
                return view('pages.area.columns._actions', compact('area'));
            })
            ->setRowId('id');
//            ->addColumn('action', 'area.action')
//            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Area $model): QueryBuilder
    {
        return $model->newQuery()->orderByDesc('id');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle();
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
            Column::make('id'),
            Column::make('name'),
            Column::make('phone'),
            Column::make('created_at'),
            Column::make('updated_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'StadiumOwner_' . date('YmdHis');
    }
}
