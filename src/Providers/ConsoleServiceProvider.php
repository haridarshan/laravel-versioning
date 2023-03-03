<?php

namespace Haridarshan\Laravel\ApiVersioning\Providers;

use Nwidart\Modules\Providers\ConsoleServiceProvider as NwidartConsoleServiceProvider;
use Haridarshan\Laravel\ApiVersioning\Commands;

class ConsoleServiceProvider extends NwidartConsoleServiceProvider
{
    public function register(): void
    {
        parent::register();

        $this->commands(
            config('api.commands.register')
        );

//        foreach (config('api.commands.override') as $signature => $commandClass) {
//            //$signature = str_replace(':', '.', $signature);
//            $this->app->extend($signature, function () use ($commandClass) {
//                return new $commandClass();
//            });
//        }
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return array_merge(parent::provides(), config('api.commands.register'));
    }
}
