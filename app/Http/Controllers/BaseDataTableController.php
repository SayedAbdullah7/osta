<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\Facades\DataTables;

class BaseDataTableController extends Controller
{
    /**
     * Prepare columns for DataTables frontend.
     */
    protected function prepareFrontendColumns(array $columns): array
    {
        return array_map(fn($column) => [
            'data' => $column['data'],
            'name' => $column['name'] ?? $column['data'],
            'title' => $column['title'] ?? ucfirst($column['data']),
            'searchable' => $column['searchable'] ?? false,
            'orderable' => $column['orderable'] ?? true,
        ], $columns);
    }

    /**
     * Handle DataTable for the backend.
     */
    protected function handleDataTable(string $modelClass, array $columns): \Illuminate\Http\JsonResponse
    {
        $query = $modelClass::query();

        $dataTable = DataTables::of($query)->addIndexColumn();

        foreach ($columns as $column) {
            if (is_callable($column['backend'] ?? null)) {
                $dataTable->editColumn($column['data'], $column['backend']);
            }
        }

        if (isset($columns['actions'])) {
            $dataTable->addColumn('actions', $columns['actions']['backend']);
        }

        return $dataTable->rawColumns(['actions'])->make(true);
    }
}
