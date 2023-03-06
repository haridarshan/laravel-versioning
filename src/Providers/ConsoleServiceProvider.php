<?php

namespace Haridarshan\Laravel\ApiVersioning\Providers;

use Nwidart\Modules\Providers\ConsoleServiceProvider as NwidartConsoleServiceProvider;

class ConsoleServiceProvider extends NwidartConsoleServiceProvider
{
    public function register(): void
    {
        parent::register();

        $this->commands(
            config('api.commands.register')
        );
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return array_merge(parent::provides(), config('api.commands.register'));
    }
}
