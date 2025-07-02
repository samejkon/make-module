<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $modulesPath = base_path('Modules');
        if (is_dir($modulesPath)) {
            foreach (scandir($modulesPath) as $module) {
                if ($module === '.' || $module === '..') continue;
                $provider = "Modules\\{$module}\\Providers\\{$module}ServiceProvider";
                $providerPath = $modulesPath . "/{$module}/Providers/{$module}ServiceProvider.php";
                if (file_exists($providerPath)) {
                    $this->app->register($provider);
                }
            }
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
