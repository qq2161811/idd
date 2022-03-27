<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd;


use IDD\Make\HttpConstants;
use IDD\Make\Commands\Make\Curd\Traits\ArrayAndString;
use IDD\Make\Commands\Make\Curd\Traits\BizFieldHandle;
use IDD\Make\Commands\Make\Curd\Traits\Controller;
use IDD\Make\Commands\Make\Curd\Traits\CurrentTableHandle;
use IDD\Make\Commands\Make\Curd\Traits\EnumType;
use IDD\Make\Commands\Make\Curd\Traits\FieldHandle;
use IDD\Make\Commands\Make\Curd\Traits\FileHandle;
use IDD\Make\Commands\Make\Curd\Traits\Model;
use IDD\Make\Commands\Make\Curd\Traits\PriceType;
use IDD\Make\Commands\Make\Curd\Traits\RoutesHandle;
use IDD\Make\Commands\Make\Curd\Traits\Service;
use IDD\Make\Commands\Make\Curd\Traits\URLHandle;
use IDD\Make\Commands\Make\Curd\Traits\Validate;
use IDD\Make\Commands\Make\Curd\Traits\YapiDocs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


/**
 * 创建curd模板
 * Class Make
 *
 * @package IDD\Make\Commands\Curd
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/14 6:15 PM
 */
class Generator extends GeneratorCommand
{
    use CurrentTableHandle, FileHandle, Controller, Service, Validate, Model, BizFieldHandle, RoutesHandle, YapiDocs,
        EnumType, FieldHandle, PriceType, URLHandle, ArrayAndString;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:curd
                            {tables : 数据表名称，使用英文逗号(,)分割多个值}
                            {--c|ctl_namespace= : 控制器命令空间，会根据该命令空间解析所有文件路径文件}
                            {--m|module='.HttpConstants::MODULES_API.' : 应用项目模块名称: System Admin Api }
                            {--f|force= : 强制重新生成所有文件，默认情况下，文件已存在则跳过生成}
                            {--s|short_name=1 : 文件生成是否使用短名称，默认会加入文件类型后缀}
                            {--b|biz_column='.CurdConstants::BIZ_ID_FIELD_NAME.' : 业务ID字段名称，数据表字段名称}
                            {--keyword_key=title : 关键字搜索字段，数据表字段}
                            {--keyword=keyword : 关键字搜索字段提交字段，前端提交的字段名称}
                            {--skip_c= : 跳过创建 controller}
                            {--skip_s= : 跳过创建 service}
                            {--skip_v= : 跳过创建 validate}
                            {--skip_m= : 跳过创建 model}
                            {--skip_yapi= : 跳过创建 yapi文档}
                            {--r|routes= : 展示模板路由}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成 curd 模板文件，会生成几个文件: controller、service、validate、model';

    protected function init(): void
    {
        $this->initModule();
        $this->getTableNameListInput();
        $ctlNamespace       = $this->getOptionWithTrim('ctl_namespace');
        $this->ctlNamespace = $this->qualifyClass($ctlNamespace);
        $this->initBizColumnName();
        $this->curdModel = app(CurdModel::class);
    }

    /**
     * 重置属性
     *
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/18 1:35 PM
     */
    protected function resetProperty(): void
    {
        $this->initBizColumnName();
        $this->currentColumnList = [];
        $this->currentTable      = null;
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException|\JsonException
     */
    public function handle(): int
    {
        $this->init();
        $makeRoutes = $this->getOptionWithTrim('routes');
        foreach ($this->inputTableNameList as $item) {
            $this->resetProperty();
            $item = trim($item);
            if (empty($item)) {
                $this->error('The datatable name cannot be empty');
                continue;
            }
            // 不允许使用 php 保留字
            if ($this->isReservedName($item)) {
                $this->error('The datatable "'.$item.'" is reserved by PHP.');
                continue;
            }
            $baseName = Str::studly($item);
            if ($this->isReservedName($baseName)) {
                $this->error('The datatable "'.$baseName.'" is reserved by PHP.');
                continue;
            }
            // 获取数据表信息
            $this->currentTable = DB::connection()->getDoctrineSchemaManager()->listTableDetails($item);
            if (is_null($this->currentTable) || empty($this->currentTable->getColumns())) {
                $this->error('The table "'.$item.'" does not exist.');
                continue;
            }
            $this->formatTableColumns();
            $ctlName                      = $this->getControllerRealName($baseName);
            $this->currentControllerClass = $this->ctlNamespace.'\\'.$ctlName;

            $makeCtlRoute = false;
            if (! $makeRoutes) {
                // 创建控制器
                if (! $this->getOptionWithTrim('skip_c')) {
                    $makeCtlRoute = $this->makeController($baseName);
                }
                // 创建服务
                if (! $this->getOptionWithTrim('skip_s')) {
                    $this->makeService($baseName);
                }
                // 创建验证器
                if (! $this->getOptionWithTrim('skip_v')) {
                    $this->makeValidate($baseName);
                }
                // 创建模型
                if (! $this->getOptionWithTrim('skip_m')) {
                    $this->makeModel($baseName);
                }
                // 创建 yapi 文档
                if (! $this->getOptionWithTrim('skip_yapi')) {
                    $this->makeYapiDoc();
                }
            }
            // 生成展示路由
            if ($makeCtlRoute || $makeRoutes) {
                $this->routes[$ctlName] = $this->getRoutes();
            }
        }

        $strIndent = $this->getStrIndent(60, '-');
        if ($this->createdFileList) {
            // 展示创建成功的文件
            $this->warn(sprintf("\n%s created files %s\n", $strIndent, $strIndent));
            foreach ($this->createdFileList as $type => $files) {
                foreach ($files as $file) {
                    $this->info(sprintf('%s %s  created successfully.', $type, $file));
                }
            }
            $this->warn(sprintf("\n%s created files %s\n", $strIndent, $strIndent));
        } elseif (! $makeRoutes) {
            $this->error('Failed to create all files.');
        }

        // 展示模板路由
        if ($this->routes) {
            $this->warn(sprintf("\n%s routes example %s\n", $strIndent, $strIndent));
            $spaceIndent = $this->getStrIndent(4);
            foreach ($this->routes as $key => $route) {
                $this->line(sprintf("%s%s\n%s\n", $spaceIndent, $key, $route));
            }
            $this->warn(sprintf("\n%s routes example %s\n", $strIndent, $strIndent));
        }

        return 0;
    }

    /**
     * 获取当前文件所在路径
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 2:56 PM
     */
    protected function getPwd(): string
    {
        return $this->laravel['path'].'/Console/Commands/Make/Curd';
    }

    /**
     * 文件生成是否使用短名称
     *
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 2:01 PM
     */
    protected function isUseShortName(): bool
    {
        return empty($this->getOptionWithTrim('short_name'));
    }

    /**
     * 初始化模块名
     *
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 6:44 PM
     */
    protected function initModule(): void
    {
        $this->module = (string) $this->getOptionWithTrim('module');
        if (! in_array($this->module, HttpConstants::MODULES_LIST, true)) {
            throw new \InvalidArgumentException('The module "'.$this->module.'" is not supported.');
        }
    }

    /**
     * 获取基类
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 6:57 PM
     */
    protected function getBaseClass(): array
    {
        $baseList = [
            HttpConstants::MODULES_ADMIN => [
                HttpConstants::CONTROLLER_SUFFIX => $this->parseClassNamespaceAndName(\App\Http\Admin\Controllers\BaseController::class),
                HttpConstants::SERVICE_SUFFIX    => $this->parseClassNamespaceAndName(\App\Http\Admin\Services\BaserService::class),
                HttpConstants::VALIDATE_SUFFIX   => $this->parseClassNamespaceAndName(\App\Http\Admin\Validates\BaseValidate::class),
            ],
            HttpConstants::MODULES_API    => [
                HttpConstants::CONTROLLER_SUFFIX => $this->parseClassNamespaceAndName(\App\Http\Api\Controllers\BaseController::class),
                HttpConstants::SERVICE_SUFFIX    => $this->parseClassNamespaceAndName(\App\Http\Api\Services\BaseService::class),
                HttpConstants::VALIDATE_SUFFIX   => $this->parseClassNamespaceAndName(\App\Http\Api\Validates\BaseValidate::class),
            ],
        ];

        return $baseList[$this->module];
    }

    /**
     * 解析类地址与类名称
     *
     * @param  string  $baseName
     * @param  string  $replaceNamespace
     * @param  string  $classSuffix
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 10:06 AM
     */
    protected function parseClass(string $baseName, string $replaceNamespace, string $classSuffix): array
    {
        $ctlName         = $this->getControllerRealName($baseName);
        $className       = $this->getRealName($baseName, $classSuffix);
        $ctlClass        = $this->currentControllerClass;
        $tempHandleClass = str_replace($ctlName, $className, $ctlClass);
        $handleClassName = str_replace(HttpConstants::PATH_NAME_CONTROLLER, $replaceNamespace, $tempHandleClass);

        return $this->parseClassNamespaceAndName($handleClassName);
    }

    /**
     * 根据类地址解析类名空间与名称
     *
     * @param  string  $class
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 7:05 PM
     */
    protected function parseClassNamespaceAndName(string $class): array
    {
        $classArr = explode('\\', $class);
        if (! $classArr) {
            return [];
        }

        return [end($classArr), $class];
    }

    /**
     * 获取选项值 -- 字符串时，使用 trim 过滤
     *
     * @param  string  $key
     * @return array|bool|string|null
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/18 1:23 PM
     */
    protected function getOptionWithTrim(string $key)
    {
        $option = $this->option($key);
        if (is_string($option)) {
            $option = trim($option);
        }

        return $option;
    }

    /**
     * 是否为 new 对象操作
     *
     * @param  string  $str
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 3:26 PM
     */
    protected function isNewObject(string $str): bool
    {
        return str_starts_with($str, 'new \\');
    }
}
