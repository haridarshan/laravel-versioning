<?php

namespace Haridarshan\Laravel\ApiVersioning\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Module;
use Nwidart\Modules\Support\Stub;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'module:bootstrap')]
class BootstrapMakeCommand extends Command
{
    /**
     * @var string
     */
    protected string $argumentName = 'module';

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'module:bootstrap';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a new bootstrap file for the specified module.';

    /**
     * @return int
     */
    public function handle(): int
    {
        $modules = $this->argument('module');
        if (empty($modules)) {
            $modules = $this->laravel['modules']->all();
        }

        $success = true;
        foreach ($modules as $module) {
            if (!$module instanceof Module) {
                $module = $this->laravel['modules']->findOrFail(Str::studly($module));
            }
            $code = $this->generateFiles($module);

            if ($code === E_ERROR) {
                $success = false;
            }
        }

        return $success ? 0 : E_ERROR;
    }

    /**
     * Generate the files.
     *
     * @param Module $module
     *
     * @return int
     */
    public function generateFiles(Module $module): int
    {
        foreach ($this->getFiles() as $stub => $file) {
            $path = $module->getPath() . '/' . $file;
            $this->components->task("Generating file $path", function () use ($stub, $path) {
                if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
                    $this->laravel['files']->makeDirectory($dir, 0775, true);
                }

                $this->laravel['files']->put($path, $this->getStubContents($stub));
            });
        }

        return 0;
    }

    /**
     * Get the list of files that will be created.
     *
     * @return array
     */
    public function getFiles(): array
    {
        return config('api.stubs.files');
    }

    /**
     * Get the contents of the specified stub file by given stub name.
     *
     * @param $stubFile
     *
     * @return string
     */
    protected function getStubContents($stubFile): string
    {
        $stub = Stub::create(
            '/' . $stubFile . '.stub',
            $this->getReplacement($stubFile)
        );
        $stub::setBasePath(config('api.stubs.path'));
        return $stub->render();
    }

    /**
     * Get array replacement for the specified stub.
     *
     * @param $stub
     *
     * @return array
     */
    protected function getReplacement($stub): array
    {
        $replacements = config('api.stubs.replacements');

        if (!isset($replacements[$stub])) {
            return [];
        }

        $keys = $replacements[$stub];

        $replaces = [];

        if ($stub === 'json' || $stub === 'composer') {
            if (in_array('PROVIDER_NAMESPACE', $keys, true) === false) {
                $keys[] = 'PROVIDER_NAMESPACE';
            }
        }
        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get' . ucfirst(Str::studly(strtolower($key))) . 'Replacement')) {
                $replaces[$key] = $this->$method();
            } else {
                $replaces[$key] = null;
            }
        }

        return $replaces;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::IS_ARRAY, 'The name of module will be used.'],
        ];
    }
}
