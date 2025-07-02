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

        $providerContent = "<?php\n\nnamespace Modules\\{$name}\\Providers;\n\nuse Illuminate\\Support\\ServiceProvider;\n\nclass {$name}ServiceProvider extends ServiceProvider\n{\n    /**\n     * Register the service provider.\n     *\n     * @return void\n     */\n    public function register()\n    {\n        \$this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');\n        \$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');\n        \$this->loadViewsFrom(__DIR__ . '/../Resources/views', '{$routeName}'); // Tên view alias cũng nên là chữ thường
        \$this->loadMigrationsFrom(__DIR__ . '/../database/migrations');\n        \$this->mergeConfigFrom(__DIR__ . '/../config/config.php', '{$routeName}');
    }\n\n    /**\n     * Bootstrap services.\n     *\n     * @return void\n     */\n    public function boot()\n    {\n        //
    }\n}\n";
        $this->files->put("{$modulePath}/Providers/{$name}ServiceProvider.php", $providerContent);

        $configContent = "<?php\n
        return [
           'name' => '{$new_name}'\n];\n";
        $this->files->put("{$modulePath}/config/config.php", $configContent);

        $webRouteContent = "<?php\n
        use Illuminate\\Support\\Facades\\Route;\n";
        $this->files->put("{$modulePath}/Routes/web.php", $webRouteContent);

        $apiRouteContent = "<?php\n
        use Illuminate\\Support\\Facades\\Route;\n";
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
