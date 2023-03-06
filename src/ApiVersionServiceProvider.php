<?php

namespace Haridarshan\Laravel\ApiVersioning;

use Haridarshan\Laravel\ApiVersioning\Providers\ConsoleServiceProvider;
use Illuminate\Routing\Router;
use Nwidart\Modules\LaravelModulesServiceProvider;

class ApiVersionServiceProvider extends LaravelModulesServiceProvider
{
    /**
     * Bootstrap the package's services.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $configPath = __DIR__ . '/../config/config.php';
        $stubsPath = dirname(__DIR__) . '/src/Commands/stubs';

        $this->publishes([
            $configPath => config_path('api.php'),
        ], 'api');

        $this->publishes([
            $stubsPath => base_path('stubs/api-stubs'),
        ], 'api-stubs');

        $this->registerMiddlewareAliases();
    }

    /**
     * Register the package's services.
     *
     * @return void
     */
    public function register(): void
    {
        parent::register();

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
