<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class GenerateDataTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:datatable {model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a custom DataTable class based on a model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $modelName = $this->argument('model');
        $className = Str::studly($modelName) . 'DataTable';
        $filePath = app_path("DataTables/Custom/{$className}.php");

        // Ensure the directory exists
        if (!File::exists(dirname($filePath))) {
            File::makeDirectory(dirname($filePath), 0755, true);
        }

        // Check if the file already exists
        if (File::exists($filePath)) {
            $this->error("The file {$className}.php already exists!");
            return;
        }

        // Fetch table columns
        $columns = $this->getModelTableColumns($modelName);
        if (!$columns) {
            $this->error("Unable to fetch columns for the model '{$modelName}'. Ensure the model and table exist.");
            return;
        }

        // Generate the content
        $content = $this->getStubContent($className, $columns,$modelName);

        // Write the content to the file
        File::put($filePath, $content);

        $this->info("DataTable class {$className} created successfully at {$filePath}");
    }

    /**
     * Fetch the columns of the model's table.
     *
     * @param string $modelName
     * @return array|null
     */
    private function getModelTableColumns(string $modelName): ?array
    {
        $modelClass = "App\\Models\\" . Str::studly($modelName);

        if (!class_exists($modelClass)) {
            return null;
        }

        $model = new $modelClass();
        $table = $model->getTable();

        return Schema::hasTable($table) ? Schema::getColumnListing($table) : null;
    }

    /**
     * Get the content of the stub.
     *
     * @param string $className
     * @param array $columns
     * @return string
     */
    private function getStubContent(string $className, array $columns,$modelName): string
    {
        $columnDefinitions = collect($columns)
            ->map(fn ($column) => "            Column::create('{$column}'),")
            ->implode("\n");

        return <<<EOT
<?php

namespace App\DataTables\Custom;

use App\Helpers\Column;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\Filter;
use App\Models\\$modelName;

class {$className} extends BaseDataTable
{
    /**
     * Define searchable relations for the query.
     */
    protected array \$searchableRelations = [
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
{$columnDefinitions}
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
        \$query = {$modelName}::query();

        return DataTables::of(\$query)
            ->filter(fn (\$query) => \$this->applySearch(\$query))
            ->make(true);
    }
}
EOT;
    }
}
