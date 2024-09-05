<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
//    protected $signature = 'app:make-repository';

    /**
     * The console command description.
     *
     * @var string
     */
//    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    protected $signature = 'make:repository {name}';
    protected $description = 'Create a new repository class in the app/Repositories directory';

    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path('Repositories/' . $name . 'Repository.php');

        if (File::exists($path)) {
            $this->error('Repository class already exists!');
            return 1;
        }

        $stub = $this->getStub();
        $stub = str_replace('{{ class }}', $name, $stub);

        File::put($path, $stub);

        $this->info('Repository class created successfully.');
        return 0;
    }

    protected function getStub()
    {
        return <<<'EOD'
<?php

namespace App\Repositories;

class {{ class }}Repository
{
    // Add repository methods here
}
EOD;
    }
}
