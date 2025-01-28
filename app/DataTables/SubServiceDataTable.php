<?php

namespace App\DataTables;

use App\Models\Service;
use App\Models\SubService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SubServiceDataTable extends DataTable
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
            ->addColumn('action', function ($subService) {
//                return view('sub_services.actions', compact('subService'));
                $service = $subService;
                return view('pages.sub-service.columns._actions', compact('subService', 'service'));
            })
            ->editColumn('created_at', function ($subService) {
                return $subService->created_at->format('Y-m-d H:i:s'); // Adjust format as needed
            })
            ->editColumn('updated_at', function ($subService) {
                return $subService->updated_at->format('Y-m-d H:i:s'); // Adjust format as needed
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param SubService $model
     * @return QueryBuilder
     */
    public function query(SubService $model): QueryBuilder
    {
        return $model->newQuery()->with('service'); // eager load the related service
    }

    /**
     * Optional method for modifying the format of the data before it is sent to the client.
     *
     * @param mixed $row
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('name'),
            Column::make('max_price'),
            Column::make('type'),
            Column::make('service.name')->title('Service'), // Assuming a 'name' column exists on the Service model
            Column::make('created_at'),
            Column::make('updated_at'),
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
        return 'SubServices_' . date('YmdHis');
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
//            ->buttons([
//                Button::make('create'),
//                Button::make('export'),
//                Button::make('print'),
//                Button::make('reset'),
//                Button::make('reload'),
//            ]);
        return $this->builder()
            ->setTableId('subservices-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons([
                Button::make('create'),
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);

    }


}
