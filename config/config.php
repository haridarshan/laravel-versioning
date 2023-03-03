<?php

use Haridarshan\Laravel\ApiVersioning\Commands;
use Haridarshan\Laravel\ApiVersioning\Middleware\ApiVersion;

return [

    /*
    |--------------------------------------------------------------------------
    | Latest Api Version
    |--------------------------------------------------------------------------
    |
    */
    'latest' => 'v1',

    /*
    |--------------------------------------------------------------------------
    | Middleware Aliases for Route and Groups
    |--------------------------------------------------------------------------
    |
    | Route and groups middleware aliases
    |
    */
    'middlewareAliases' => [
        'api.version' => ApiVersion::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Package commands
    |--------------------------------------------------------------------------
    |
    | Here you can define which commands will be visible and used in your
    | application. If for example you don't use some of the commands provided
    | you can simply comment them out.
    |
    */
    'commands' => [
        'register' => [
            Commands\BootstrapMakeCommand::class,
            Commands\RouteProviderUpdateCommand::class,
        ],
        'override' => [

        ],
    ],

    'stubs' => [
        'enabled' => false,
        'path' => base_path('vendor/haridarshan/laravel-api-versioning/src/Commands/stubs'),
        'files' => [
            'bootstrap' => 'bootstrap.php',
        ],
        'replacements' => [],
        'gitkeep' => true,
    ],

    'paths' => [
        'generator' => [
            'provider' => ['path' => 'Providers', 'generate' => true],
        ],
    ],
];
