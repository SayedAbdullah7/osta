<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Schema;
use App\Models\User;

class MakeDataTable extends Command
{
    // Command signature to accept the model name and options for DataTable
    protected $signature = 'make:datatable {model} {--filters=*}';

    // Command description
    protected $description = 'Generate a DataTable class for a given model with custom filters.';

    public function handle()
    {
        // Get the model name and filters from the arguments/options
        $model = $this->argument('model');
        $filters = $this->option('filters');

        // Format model name to StudlyCase for class name
        $modelClass = Str::studly($model);

        // Path to save the DataTable class file
        $directoryPath = app_path("DataTables/Custom");
        $filePath = $directoryPath . "/{$modelClass}DataTable.php";

        // Ensure the directory exists
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }

        // Check if the file already exists
        if (File::exists($filePath)) {
            $this->error("DataTable file for {$modelClass} already exists.");
            return;
        }

        // Generate the DataTable content based on the user input
        $this->generateDataTable($modelClass, $filters);

        $this->info("DataTable for {$modelClass} created successfully at {$filePath}.");
    }

    private function generateDataTable($modelClass, $filters)
    {
        // Stub for the DataTable class content
        $stub = file_get_contents(app_path('Console/Commands/stubs/datatable.stub'));

        // Replace the model class and table columns in the stub
        $stub = str_replace('{{modelClass}}', $modelClass, $stub);

        // Get the columns from the model's database table
        $columns = $this->getTableColumns($modelClass);
        $formattedColumns = $this->formatColumns($columns);
        $stub = str_replace('{{columns}}', $formattedColumns, $stub);

        // Format filters if provided
        $formattedFilters = $this->formatFilters($filters);
        $stub = str_replace('{{filters}}', $formattedFilters, $stub);

        // Create the directory if it doesn't exist
        $directoryPath = app_path("DataTables/Custom");
        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0755, true);
        }

        // Write the final generated class to the DataTables directory
        File::put(app_path("DataTables/Custom/{$modelClass}DataTable.php"), $stub);
    }

    // Get columns from the database based on the model's table
    private function getTableColumns($modelClass)
    {
        $model = "App\\Models\\" . $modelClass;
        $table = (new $model)->getTable();  // Get the table name from the model

        // Get column names from the table
        return Schema::getColumnListing($table);
    }

    // Format columns for DataTable generation
    private function formatColumns($columns)
    {
        $formatted = array_map(function ($column) {
            return "            ['data' => '$column', 'name' => '$column', 'title' => '" . ucwords(str_replace('_', ' ', $column)) . "', 'searchable' => true],";
        }, $columns);

        return implode("\n", $formatted);
    }

    // Format filters for DataTable generation
    private function formatFilters($filters)
    {
        if (empty($filters)) {
            return ''; // Return empty if no filters are provided
        }

        $formatted = array_map(function ($filter) {
            return "            '$filter' => [\n"
                . "                'type' => 'select',\n"
                . "                'placeholder' => 'Select $filter',\n"
                . "                'options' => [\n"
                . "                    ['key' => 'option1', 'value' => 'Option 1'],\n"
                . "                    ['key' => 'option2', 'value' => 'Option 2'],\n"
                . "                ],\n"
                . "            ],";
        }, $filters);

        return implode("\n", $formatted);
    }
}
