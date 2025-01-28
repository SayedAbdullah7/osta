<?php

namespace App\DataTables;

use App\Models\Review;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ReviewDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->of($query)
            ->addColumn('reviewable', function ($review) {
                // Show details of the reviewable entity (e.g., user or provider)
                $type = class_basename($review->reviewable_type);
                return $review->reviewable ? "{$type}: {$review->reviewable->name}" : 'N/A';
            })
            ->addColumn('reviewed', function ($review) {
                // Show details of the reviewed entity
                $type = class_basename($review->reviewed_type);
                return $review->reviewed ? "{$type}: {$review->reviewed->name}" : 'N/A';
            })
            ->addColumn('order', function ($review) {
                // Include details about the associated order
                return $review->order
                    ? "Order #{$review->order->id} "
//                    ? "Order #{$review->order->id} - Status: {$review->order->statusText()}"
                    : 'N/A';
            })
            ->editColumn('rating', function ($review) {
                // Render star rating visually
                $stars = '';
                for ($i = 1; $i <= 5; $i++) {
                    $stars .= $i <= $review->rating
                        ? '<div class="rating-label checked"><i class="ki-duotone ki-star fs-1"></i></div>'
                        : '<div class="rating-label"><i class="ki-duotone ki-star fs-1"></i></div>';
                }
                return '<div class="rating">' . $stars . '</div>';
            })
            ->editColumn('is_approved', function ($review) {
                // Display approval status with icons
                return $review->is_approved
                    ? '<i class="fa-solid fa-check-circle text-success fs-1"></i>'
                    : '<i class=" fa-solid fa-times-circle text-danger fs-1"></i>';
            })
            ->addColumn('created_at', function ($review) {
                // Show the creation date in a readable format
                return $review->created_at->format('Y-m-d H:i:s');
            })
            ->addColumn('action', function ($model) {
                // Include detailed actions for the review
                return view('pages.review.columns._actions', compact('model'));
            })
            ->rawColumns(['rating', 'is_approved', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Review $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Review $model)
    {
        return $model->with(['reviewable', 'reviewed', 'order']);
    }

    /**
     * Get columns for DataTable.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('id')->title('ID'),
            Column::make('order')->title('Order Details'),
            Column::make('reviewable')->title('Reviewer '),
            Column::make('reviewed')->title('Reviewed '),
            Column::make('comment')->title('Comment'),
            Column::make('rating')->title('Rating'),
            Column::make('is_approved')->title('Approved')->addClass('text-center text-nowrap'),
            Column::make('created_at')->title('Created At'),
            Column::computed('action')
                ->addClass('text-center text-nowrap')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->title('Actions'),
        ];
    }

    /**
     * Build the HTML for the DataTable.
     *
     * @param \Yajra\DataTables\Html\Builder $htmlBuilder
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('reviews-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt' . "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>",)
            ->addTableClass('table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer text-gray-600 fw-semibold')
            ->setTableHeadClass('text-start text-muted fw-bold fs-7 text-uppercase gs-0')
            ->orderBy(0, 'desc');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Reviews_' . date('YmdHis');
    }
}
