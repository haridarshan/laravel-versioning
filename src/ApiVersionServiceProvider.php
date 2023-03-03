<?php

namespace Haridarshan\Laravel\ApiVersioning;

use Haridarshan\Laravel\ApiVersioning\Providers\ConsoleServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Nwidart\Modules\Exceptions\ModuleNotFoundException;

class ApiVersionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the package's services.
     *
     * @return void
     * @throws ModuleNotFoundException
     */
    public function boot(): void
    {
        if (config('modules') === null) {
            throw new ModuleNotFoundException(
                "Run 'php artisan vendor:publish --provider=\"Nwidart\Modules\LaravelModulesServiceProvider\"' first"
            );
        }

        $configPath = __DIR__ . '/../config/config.php';
        $stubsPath = dirname(__DIR__) . '/src/Commands/stubs';

        $this->publishes([
            $configPath => config_path('api.php'),
        ], 'config');

        $this->publishes([
            $stubsPath => base_path('stubs/api-stubs'),
        ], 'stubs');

        $this->registerMiddlewareAliases();
    }

    /**
     * Register the package's services.
     *
     * @return void
     * @throws ModuleNotFoundException
     */
    public function register(): void
    {
        if (config('modules') === null) {
            throw new ModuleNotFoundException(
                "Run 'php artisan vendor:publish --provider=\"Nwidart\Modules\LaravelModulesServiceProvider\"' first"
            );
        }

        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'api');
        $this->registerProviders();
    }

    /**
     * Register MiddlewareAliases for routes and groups.
     *
     * @return void
     */
    public function registerMiddlewareAliases(): void
    {
        $middlewareAliases = $this->app['config']->get('api.middlewareAliases');

        /** @var Router $router */
        $router = $this->app['router'];
        foreach ($middlewareAliases as $alias => $middleware) {
            $router->aliasMiddleware($alias, $middleware);
        }
    }

    /**
     * @return void
     */
    public function registerProviders(): void
    {
        $this->app->register(ConsoleServiceProvider::class);
    }
}
