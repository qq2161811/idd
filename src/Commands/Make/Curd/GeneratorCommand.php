<?php

namespace IDD\Make\Commands\Make\Curd;

use IDD\Make\Commands\Make\Curd\Traits\Author;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;


abstract class GeneratorCommand extends Command
{
    use Author;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected Filesystem $files;

    /**
     * Reserved names that cannot be used for generation.
     *
     * @var string[]
     */
    protected array $reservedNames = [
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'final',
        'finally',
        'fn',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
        'yield',
    ];

    /**
     * 数据表名称集合
     *
     * @var array
     */
    protected array $inputTableNameList = [];

    /**
     * 控制器命令空间
     *
     * @var string
     */
    protected string $ctlNamespace = '';

    /**
     * 已成功创建的文件列表
     *
     * @var array
     */
    protected array $createdFileList = [];

    /**
     * 默认的业务ID字段名
     *
     * @var string
     */
    protected string $bizColumnName = CurdConstants::BIZ_ID_FIELD_NAME;

    /**
     * 默认的业务ID字段类型
     *
     * @var string
     */
    protected string $bizColumnType = CurdConstants::BIZ_ID_FIELD_NAME_TYPE;

    /**
     * 样例模型
     *
     * @var \IDD\Make\Commands\Make\Curd\CurdModel|null
     */
    protected ?CurdModel $curdModel = null;

    /**
     * 当前操作的table
     *
     * @var \Doctrine\DBAL\Schema\Table|null
     */
    protected ?Table $currentTable = null;

    /**
     * 当前控制器类地址
     *
     * @var string
     */
    protected string $currentControllerClass = '';

    /**
     * 应用模块
     *
     * @var string
     */
    protected string $module = '';

    /**
     * 当前数据表字段列表
     *
     * @var array
     */
    protected array $currentColumnList = [];

    /**
     * 路由列表
     *
     * @var array
     */
    protected array $routes = [];

    /**
     * Create a new controller creator command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass(string $name): string
    {
        $name = ltrim($name, '\\/');

        $name = str_replace('/', '\\', $name);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($name, $rootNamespace)) {
            return $name;
        }

        return $this->qualifyClass(
            $this->getDefaultNamespace(trim($rootNamespace, '\\')).'\\'.$name
        );
    }

    /**
     * Qualify the given model class base name.
     *
     * @param  string  $model
     * @return string
     */
    protected function qualifyModel(string $model): string
    {
        $model = ltrim($model, '\\/');

        $model = str_replace('/', '\\', $model);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($model, $rootNamespace)) {
            return $model;
        }

        return is_dir(app_path('Models'))
            ? $rootNamespace.'Models\\'.$model
            : $rootNamespace.$model;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace(string $rootNamespace): string
    {
        return $rootNamespace;
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists(string $rawName): bool
    {
        return $this->files->exists($this->getPath($this->qualifyClass($rawName)));
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath(string $name): string
    {
        $name = Str::replaceFirst($this->rootNamespace(), '', $name);

        return $this->laravel['path'].'/'.str_replace('\\', '/', $name).'.php';
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string  $path
     * @return string
     */
    protected function makeDirectory(string $path): string
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true, true);
        }

        return $path;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass(string $name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return $this
     */
    protected function replaceNamespace(string &$stub, string $name): self
    {
        $searches = [
            ['DummyNamespace', 'DummyRootNamespace', 'NamespacedDummyUserModel'],
            ['{{ namespace }}', '{{ rootNamespace }}', '{{ namespacedUserModel }}'],
            ['{{namespace}}', '{{rootNamespace}}', '{{namespacedUserModel}}'],
        ];

        foreach ($searches as $search) {
            $stub = str_replace(
                $search,
                [$this->getNamespace($name), $this->rootNamespace(), $this->userProviderModel()],
                $stub
            );
        }

        return $this;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace(string $name): string
    {
        return trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass(string $stub, string $name): string
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace(['DummyClass', '{{ class }}', '{{class}}'], $class, $stub);
    }

    /**
     * 替换注释信息
     *
     * @param  string  $stub
     * @param  string  $classDesc
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 2:45 PM
     */
    protected function replaceDescription(string $stub, string $classDesc): string
    {
        $stub = str_replace(['{{ classDesc }}', '{{classDesc}}'], $classDesc, $stub);
        $stub = str_replace(['{{ author }}', '{{author}}'], $this->getAuthor(), $stub);

        return str_replace(['{{ authorAndTime }}', '{{authorAndTime}}'], $this->getAuthorAndTime(), $stub);
    }

    /**
     * 替换父类信息
     *
     * @param  string  $stub
     * @param  string  $type
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 10:50 AM
     */
    protected function replaceParent(string $stub, string $type): string
    {
        [$parentName, $parentNamespace] = $this->getBaseClass()[$type];

        $stub = str_replace(['{{ parentName }}', '{{parentName}}'], $parentName, $stub);

        return str_replace(['{{ parentNamespace }}', '{{parentNamespace}}'], $parentNamespace, $stub);
    }

    /**
     * Alphabetically sorts the imports for the given stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function sortImports(string $stub): string
    {
        if (preg_match('/(?P<imports>(?:use [^;]+;$\n?)+)/m', $stub, $match)) {
            $imports = explode("\n", trim($match['imports']));

            sort($imports);

            return str_replace(trim($match['imports']), implode("\n", $imports), $stub);
        }

        return $stub;
    }

    /**
     * Get the root namespace for the class.
     *
     * @return string
     */
    protected function rootNamespace(): string
    {
        return $this->laravel->getNamespace();
    }

    /**
     * Get the model for the default guard's user provider.
     *
     * @return string|null
     */
    protected function userProviderModel(): ?string
    {
        /** @var \Illuminate\Support\Facades\Config $config */
        $config = $this->laravel['config'];

        $provider = $config->get('auth.guards.'.$config->get('auth.defaults.guard').'.provider');

        return $config->get("auth.providers.{$provider}.model");
    }

    /**
     * Checks whether the given name is reserved.
     *
     * @param  string  $name
     * @return bool
     */
    protected function isReservedName(string $name): bool
    {
        $name = strtolower($name);

        return in_array($name, $this->reservedNames, true);
    }

    /**
     * 获取 HTTP 目录基础命名空间
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/18 2:54 PM
     */
    protected function getDefaultNamespaceWithController(): string
    {
        return $this->rootNamespace().'Http';
    }
}
