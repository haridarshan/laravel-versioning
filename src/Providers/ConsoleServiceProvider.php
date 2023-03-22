<?php

namespace Haridarshan\Laravel\NwidartModules\Providers;

use Nwidart\Modules\Providers\ConsoleServiceProvider as NwidartConsoleServiceProvider;
use Haridarshan\Laravel\NwidartModules\Commands;

class ConsoleServiceProvider extends NwidartConsoleServiceProvider
{
    protected array $registerCommands = [
        Commands\BootstrapMakeCommand::class,
        Commands\RouteProviderUpdateCommand::class,
        Commands\ModuleMakeWithVersion::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->commands(
            config('api.commands.register', $this->registerCommands)
        );
    }

    /**
     * @return array
     */
    public function provides(): array
    {
        return array_merge(parent::provides(), config('api.commands.register', $this->registerCommands));
    }
}
