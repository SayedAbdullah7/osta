<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeRepositoryAndService extends Command
{
    protected $signature = 'make:repo-service {model}';
    protected $description = 'Create a repository, interface, and service class for a given model';

    public function handle()
    {
        $model = $this->argument('model');
        $repositoryPath = app_path('Repositories/' . $model . 'Repository.php');
        $interfacePath = app_path('Repositories/Interfaces/' . $model . 'RepositoryInterface.php');
        $servicePath = app_path('Services/' . $model . 'Service.php');

        $created = false;

        // Create Repository if not exists
        if (!File::exists($repositoryPath)) {
            $repositoryStub = $this->getRepositoryStub();
            $repositoryStub = str_replace('{{ model }}', $model, $repositoryStub);
            File::put($repositoryPath, $repositoryStub);
            $this->info('Repository class created successfully.');
            $created = true;
        } else {
            $this->warn('Repository class already exists: ' . $repositoryPath);
        }

        // Create Interface if not exists
        if (!File::exists($interfacePath)) {
            $interfaceStub = $this->getInterfaceStub();
            $interfaceStub = str_replace('{{ model }}', $model, $interfaceStub);
            File::put($interfacePath, $interfaceStub);
            $this->info('Interface created successfully.');
            $created = true;
        } else {
            $this->warn('Interface already exists: ' . $interfacePath);
        }

        // Create Service if not exists
        if (!File::exists($servicePath)) {
            $serviceStub = $this->getServiceStub();
            $serviceStub = str_replace('{{ model }}', $model, $serviceStub);
            File::put($servicePath, $serviceStub);
            $this->info('Service class created successfully.');
            $created = true;
        } else {
            $this->warn('Service class already exists: ' . $servicePath);
        }

        if (!$created) {
            $this->info('No new files were created.');
        }

        return 0;
    }

    protected function getRepositoryStub()
    {
        return <<<'EOD'
<?php

namespace App\Repositories;

class {{ model }}Repository
{
    // Add repository methods here
}
EOD;
    }

    protected function getInterfaceStub()
    {
        return <<<'EOD'
<?php

namespace App\Repositories\Interfaces;

interface {{ model }}RepositoryInterface
{
    // Define interface methods here
}
EOD;
    }

    protected function getServiceStub()
    {
        return <<<'EOD'
<?php

namespace App\Services;

class {{ model }}Service
{
    // Add service methods here
}
EOD;
    }
}
