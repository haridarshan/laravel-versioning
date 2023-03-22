# laravel-modules wise composer dependencies

Manage module specific composer dependencies for Api Modules in Laravel using nWidart/laravel-modules

`laravel-modules-composer-deps` is extended from `nWidart/laravel-modules` to add support for module-wise composer-based
dependencies specifically for `--api` modules where developer may need to have different versions of same dependency for
specific module.

## Installation

1. Via [Composer](https://getcomposer.org/).

    ```php
    composer require haridarshan/laravel-modules-composer-deps
    ```

2. Publish the config (Optional)

    ```php
    php artisan vendor:publish --provider="Haridarshan\Laravel\NwidartModules\NwidartModulesServiceProvider"
    ```

   This will automatically publish configuration of `nWidart/laravel-modules` as well.

## Usage

### Create new module

Before creating a new module, please
refer [nWidart/laravel-modules documentation](https://docs.laravelmodules.com/v10/creating-a-module) for artisan command
flags.

```php
php artisan module:make:version v1 --api
```

### To update existing module(s)

* All
    ```php
    php artisan module:bootstrap
    php artisan module:update:route-provider
    ```
* Specific
    ```php
    php artisan module:bootstrap v1
    php artisan module:update:route-provider v1
    ```




