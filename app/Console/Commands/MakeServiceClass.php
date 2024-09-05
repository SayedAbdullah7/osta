<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File; // Add this line

class MakeServiceClass extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class in the app/Services directory';
    /**
     * Execute the console command.
     */


    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path('Services/' . $name . '.php');

        if (File::exists($path)) {
            $this->error('Service class already exists!');
            return 1;
        }

        $stub = $this->getStub();
        $stub = str_replace('{{ class }}', $name, $stub);

        File::put($path, $stub);

        $this->info('Service class created successfully.');
        return 0;
    }

    protected function getStub()
    {
        return <<<'EOD'
<?php

namespace App\Services;

class {{ class }}
{
    // Add your service methods here
}
EOD;
    }
}
