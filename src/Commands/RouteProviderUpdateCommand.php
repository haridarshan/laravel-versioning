<?php

namespace Haridarshan\Laravel\ApiVersioning\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;
use Nwidart\Modules\Module;
use Nwidart\Modules\Support\Config\GeneratorPath;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'module:update:route-provider')]
class RouteProviderUpdateCommand extends Command
{
    use ModuleCommandTrait;

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
        $modules = $this->laravel['modules']->all();
        $success = true;
        foreach ($modules as $module) {
            $moduleName = $module->getName();
            $path = str_replace('\\', '/', $this->getDestinationFilePath($moduleName));

            if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {
                $this->laravel['files']->makeDirectory($dir, 0777, true);
            }

            $contents = $this->getTemplateContents($moduleName);

            try {
                $this->components->task("Generating file {$path}", function () use ($path, $contents) {
                    $overwriteFile = $this->hasOption('force') ? $this->option('force') : false;

                    (new FileGenerator($path, $contents))->withFileOverwrite($overwriteFile)->generate();
                });
            } catch (FileAlreadyExistException $e) {
                $this->components->error("File : {$path} already exists.");
                $success = false;
            }
        }

        return $success ? 0 : E_ERROR;
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the file already exists.'],
        ];
    }

    /**
     * Get template contents.
     *
     * @param string $moduleName
     *
     * @return string
     */
    protected function getTemplateContents(string $moduleName): string
    {
        $module = $this->laravel['modules']->findOrFail($moduleName);

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
     * @param string $moduleName
     *
     * @return string
     */
    protected function getDestinationFilePath(string $moduleName): string
    {
        $path = $this->laravel['modules']->getModulePath($moduleName);

        $generatorPath = new GeneratorPath(config("api.paths.generator.provider"));

        return $path . $generatorPath->getPath() . '/' . $this->getFileName() . '.php';
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
}
