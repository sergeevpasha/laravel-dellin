<?php

declare(strict_types=1);

namespace SergeevPasha\Dellin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use SergeevPasha\Dellin\Libraries\DellinClient;

class DellinServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/dellin.php', 'dellin');
        $this->app->singleton(DellinClient::class, function () {
            return new DellinClient(config('dellin.key'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'dellin');
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/dellin.php' => config_path('dellin.php'),
            ], 'config');
        }
    }

    /**
     * Register routes
     *
     * @return void
     */
    protected function registerRoutes(): void
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    /**
     * Routes Configuration
     *
     * @return array
     */
    protected function routeConfiguration(): array
    {
        return [
            'prefix' => config('dellin.prefix'),
            'middleware' => config('dellin.middleware'),
        ];
    }
}
