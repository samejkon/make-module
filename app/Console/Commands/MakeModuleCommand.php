<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module structure';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $modulePath = base_path('Modules/' . $name);

        if ($this->files->isDirectory($modulePath)) {
            $this->error('Module already exists!');
            return 1;
        }

        $this->info("Creating module: {$name}...");

        $this->files->makeDirectory($modulePath, 0755, true, true);

        $directories = [
            'config',
            'database/Migrations',
            'database/Seeders',
            'database/Factories',
            'Http/Controllers',
            'Http/Middleware',
            'Http/Requests',
            'Providers',
            'Resources/assets',
            'Resources/lang',
            'Resources/views',
            'Routes',
            'Services',
        ];

        foreach ($directories as $directory) {
            $this->files->makeDirectory($modulePath . '/' . $directory, 0755, true, true);
        }

        $this->createModuleFiles($modulePath, $name);

        $this->info('Module created successfully!');
        return 0;
    }

    /**
     * Create basic module files.
     *
     * @param string $modulePath
     * @param string $name
     * @return void
     */
    protected function createModuleFiles(string $modulePath, string $name)
    {
        $routeName = strtolower($name);
        $new_name = $routeName . 's';

        $providerContent = <<<PHP
<?php

namespace Modules\\{$name}\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$name}ServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Nạp route, view, migration, config cho module
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
        \$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        \$this->loadViewsFrom(__DIR__ . '/../Resources/views', '{$routeName}');
        \$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        \$this->mergeConfigFrom(__DIR__ . '/../config/config.php', '{$routeName}');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
PHP;
        $this->files->put("{$modulePath}/Providers/{$name}ServiceProvider.php", $providerContent);

        $configContent = <<<PHP
<?php

return [
    'name' => '{$new_name}'
];
PHP;
        $this->files->put("{$modulePath}/config/config.php", $configContent);

        $webRouteContent = <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;

// Định nghĩa route cho module tại đây
PHP;
        $this->files->put("{$modulePath}/Routes/web.php", $webRouteContent);

        $apiRouteContent = <<<PHP
<?php

use Illuminate\\Support\\Facades\\Route;

// Định nghĩa route API cho module tại đây
PHP;
        $this->files->put("{$modulePath}/Routes/api.php", $apiRouteContent);

        $moduleJsonContent = json_encode([
            'name' => $name,
            'alias' => $routeName,
            'description' => '',
            'keywords' => [],
            'active' => 1,
            'order' => 0,
            'providers' => [
                "Modules\\{$name}\\Providers\\{$name}ServiceProvider"
            ],
            'files' => []
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->files->put("{$modulePath}/module.json", $moduleJsonContent);

        $composerJsonContent = json_encode([
            'name' => 'modules/' . $routeName,
            'description' => '',
            'authors' => [
                [
                    'name' => 'samejkon',
                    'email' => 'lequocphaikql@gmail.com'
                ]
            ],
            'extra' => [
                'laravel' => [
                    'providers' => [
                        "Modules\\{$name}\\Providers\\{$name}ServiceProvider"
                    ],
                    'aliases' => [],
                ],
            ],
            'autoload' => [
                'psr-4' => [
                    "Modules\\{$name}\\" => ''
                ]
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->files->put("{$modulePath}/composer.json", $composerJsonContent);
    }
}
