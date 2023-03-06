<?php

namespace Haridarshan\Laravel\ApiVersioning\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Module;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'module:update:route-provider')]
class RouteProviderUpdateCommand extends Command
{
    use ModuleCommandTrait;

    /**
     * @var string
     */
    protected string $argumentName = 'module';

    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'module:update:route-provider';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Update route service provider for the specified module.';

    /**
     * @return int
     */
    public function handle(): int
    {
        $modules = $this->argument('module');
        if (empty($modules)) {
            $modules = $this->laravel['modules']->all();
        }

        foreach ($modules as $module) {
            if (!$module instanceof Module) {
                $module = $this->laravel['modules']->findOrFail(Str::studly($module));
            }

            $path = str_replace('\\', '/', $this->getDestinationFilePath($module));

            if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
                $this->laravel['files']->makeDirectory($dir, 0777, true);
            }

            $contents = $this->getTemplateContents($module);

            $this->components->task("Generating file $path", function () use ($path, $contents) {
                (new FileGenerator($path, $contents))->withFileOverwrite(true)->generate();
            });
        }

        return 0;
    }

    /**
     * Get template contents.
     *
     * @param Module $module
     *
     * @return string
     */
    protected function getTemplateContents(Module $module): string
    {
        $stub = Stub::create(
            '/route-provider.stub',
            [
                'NAMESPACE' => $this->getClassNamespace($module, 'module'),
                'CLASS' => $this->getFileName(),
                'MODULE_NAMESPACE' => $this->laravel['modules']->config('namespace'),
                'MODULE' => $module->getStudlyName(),
                'CONTROLLER_NAMESPACE' => $this->getControllerNameSpace(),
                'WEB_ROUTES_PATH' => $this->getWebRoutesPath(),
                'API_ROUTES_PATH' => $this->getApiRoutesPath(),
                'LOWER_NAME' => $module->getLowerName(),
            ]
        );

        $stub::setBasePath(config('api.stubs.path'));
        return $stub->render();
    }

    /**
     * @return string
     */
    private function getFileName(): string
    {
        return 'RouteServiceProvider';
    }

    /**
     * Get the destination file path.
     *
     * @param Module $module
     *
     * @return string
     */
    protected function getDestinationFilePath(Module $module): string
    {
        //$path = $this->laravel['modules']->getModulePath($moduleName);
        $path = $module->getPath();

        $generatorPath = new GeneratorPath(config("api.paths.generator.provider"));

        return $path . '/' . $generatorPath->getPath() . '/' . $this->getFileName() . '.php';
    }

    /**
     * @return string
     */
    protected function getWebRoutesPath(): string
    {
        return '/' . $this->laravel['modules']->config('stubs.files.routes/web', 'Routes/web.php');
    }

    /**
     * @return string
     */
    protected function getApiRoutesPath(): string
    {
        return '/' . $this->laravel['modules']->config('stubs.files.routes/api', 'Routes/api.php');
    }

    /**
     * Get class name.
     *
     * @param string $moduleName
     * @return string
     */
    public function getClass(string $moduleName): string
    {
        return class_basename($moduleName);
    }

    /**
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        $module = $this->laravel['modules'];

        return $module->config('paths.generator.provider.namespace') ?: $module->config(
            'paths.generator.provider.path',
            'Providers'
        );
    }

    /**
     * @return string
     */
    private function getControllerNameSpace(): string
    {
        $module = $this->laravel['modules'];

        return str_replace(
            '/',
            '\\',
            $module->config('paths.generator.controller.namespace') ?: $module->config(
                'paths.generator.controller.path',
                'Controller'
            )
        );
    }

    /**
     * Get class namespace.
     *
     * @param Module $module
     * @param string $moduleName
     *
     * @return string
     */
    public function getClassNamespace(Module $module, string $moduleName): string
    {
        $extra = str_replace(
            $this->getClass('module'),
            '',
            $moduleName
        );

        $extra = str_replace('/', '\\', $extra);

        $namespace = $this->laravel['modules']->config('namespace');

        $namespace .= '\\' . $module->getStudlyName();

        $namespace .= '\\' . $this->getDefaultNamespace();

        $namespace .= '\\' . $extra;

        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::IS_ARRAY, 'The name of module will be used.'],
        ];
    }
}
