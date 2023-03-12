<?php

namespace Haridarshan\Laravel\ApiVersioning\Commands;

use Haridarshan\Laravel\ApiVersioning\Json;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use Nwidart\Modules\Commands\ModuleMakeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'module:make:version')]
class ModuleMakeWithVersion extends ModuleMakeCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new module with module-specific dependencies.';

    /**
     * @return int
     */
    public function handle(): int
    {
        if (empty($this->argument('name'))) {
            $this->components->error("Provide Module name(s) to create!");
            return E_ERROR;
        }

        $names = $this->argument('name');
        $success = true;
        foreach ($names as $name) {
            $this->components->info("Creating module with specific composer/npm dependencies: [$name]");

            $code = $this->call('module:make', [
                'name' => [$name],
                '--force' => $this->option('force'),
                '--disabled' => $this->option('disabled'),
                '--plain' => $this->option('plain'),
                '--web' => $this->option('web'),
                '--api' => $this->option('api')
            ]);

            if ($code !== 0) {
                $this->components->error("ERROR: Failed to create module: [$name]");
                return E_ERROR;
            }

            $moduleNamespace = $this->getModuleNamespace($name);
            $composerFile = Json::make('composer.json');
            $autoload = $composerFile->get('autoload');

            if (!isset($autoload['psr-4'][$moduleNamespace])) {
                $this->components->info("Register module namespace $moduleNamespace to base composer");
                $autoload['psr-4'][$moduleNamespace] = Str::studly($name) . '/';
                $composerFile->set('autoload', $autoload);
                $composerFile->save();
            } else {
                $this->components->info("Module namespace $moduleNamespace already registered in base composer");
            }

            $code = $this->call('module:update:route-provider', [
                'module' => [$name]
            ]);
            if ($code === E_ERROR) {
                $success = false;
                continue;
            }

            $code = $this->call('module:bootstrap', [
                'module' => [$name],
            ]);
            if ($code === E_ERROR) {
                $success = false;
            }
        }

        if ($success) {
            // Run composer dump-autoload
            $this->components->info("Running composer dump-autoload");
            $composer = new Composer($this->laravel['files']);
            $composer->dumpAutoloads();
        }

        return $success ? 0 : E_ERROR;
    }

    /**
     * Get Module Namespace
     *
     * @param string $moduleName
     *
     * @return string
     */
    public function getModuleNamespace(string $moduleName): string
    {
        return config('modules.namespace') . '\\' . Str::studly($moduleName) . '\\';
    }
}
