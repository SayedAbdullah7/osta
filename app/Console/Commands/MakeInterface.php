<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\File;
class MakeInterface extends Command
{
    protected $signature = 'make:interface {name}';
    protected $description = 'Create a new interface in the app/Repositories directory';

    public function handle()
    {
        $name = $this->argument('name');
        $path = app_path('Repositories/' . $name . 'Interface.php');

        if (File::exists($path)) {
            $this->error('Interface already exists!');
            return 1;
        }

        $stub = $this->getStub();
        $stub = str_replace('{{ class }}', $name, $stub);

        File::put($path, $stub);

        $this->info('Interface created successfully.');
        return 0;
    }

    protected function getStub()
    {
        return <<<'EOD'
<?php

namespace App\Repositories;

interface {{ class }}Interface
{
    // Define interface methods here
}
EOD;
    }
}
