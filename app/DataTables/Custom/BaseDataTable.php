<?php

namespace App\DataTables\Custom;

use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Facades\DataTables;

abstract class  BaseDataTable
{
    /**
     * Define searchable relations (relation => column).
     */
    protected array $searchableRelations = [];

    /**
     * Define columns for DataTable.
     */
    protected array $columns = [];

    /**
     * Returns an array of columns for the DataTable.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return array_map(function ($column) {
            return $this->normalizeColumnDefinition($column);
        }, $this->columns);
    }

    /**
     * Normalize column definition to ensure all required keys exist.
     *
     * @param array $column
     * @return array
     */
    private function normalizeColumnDefinition(array $column): array
    {
        return array_merge([
            'data' => $column['data'] ?? '',
            'name' => $column['name'] ?? $column['data'] ?? '',
            'title' => $column['title'] ?? ucfirst(str_replace('_', ' ', $column['data'] ?? '')),
            'searchable' => $column['searchable'] ?? true,
            'orderable' => $column['orderable'] ?? true,
        ], $column);
    }

    /**
     * Handle global search and related model searches.
     *
     * @param Builder $query
     */
    protected function applySearch(Builder $query): void
    {
        $search = request()->input('search.value');
        if (!$search) {
            return;
        }

//        foreach ($this->searchableRelations as $relation => $column) {
//            $query->orWhereHas($relation, fn ($q) => $q->where($column, 'like', "%{$search}%"));
//        }
//        foreach ($this->searchableRelations as $relation => $columns) {
//            $query->orWhereHas($relation, function ($q) use ($columns, $search) {
//                foreach ($columns as $column) {
//                    $q->orWhere($column, 'like', "%{$search}%");
//                }
//            });
//        }
//        if(count($this->searchableRelations) > 0) {
//            $query->orWhere(function ($subQuery) use ($search) {
                foreach ($this->searchableRelations as $relation => $columns) {
                    $query->orWhereHas($relation, function ($q) use ($columns, $search) {
                        $q->where(function ($q) use ($columns, $search) {
                            foreach ($columns as $column) {
                                $q->orWhere($column, 'like', "%{$search}%");
                            }
                        });
                    });
                }
//            });

//        }
    }



    /**
     * Abstract method to handle data processing.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    abstract public function handle();
}
