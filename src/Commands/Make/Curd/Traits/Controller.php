<?php

declare(strict_types = 1);

namespace IDD\Framework\Commands\Make\Curd\Traits;


use IDD\Framework\Contracts\HttpConstants;


/**
 * 控制器相关
 * Trait Controller
 *
 * @package IDD\Framework\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:06 AM
 */
trait Controller
{
    /**
     * 创建 controller 文件
     *
     * @param  string  $name
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 3:27 PM
     */
    protected function makeController(string $name): bool
    {
        $ctlName      = $this->getControllerRealName($name);
        $serviceClass = $this->parseClass($name, HttpConstants::PATH_NAME_SERVICE, HttpConstants::SERVICE_SUFFIX);

        $stub = $this->getStub(HttpConstants::CONTROLLER_SUFFIX);
        $stub = $this->replaceNamespace($stub, $this->currentControllerClass)->replaceClass($stub, $ctlName);
        $stub = $this->replaceDescription($stub, $this->getClassComment('控制器'));
        $stub = $this->replaceParent($stub, HttpConstants::CONTROLLER_SUFFIX);
        $stub = $this->replaceBizField($stub);
        $stub = str_replace(['{{ srvFullName }}', '{{srvFullName}}'], $serviceClass[1], $stub);

        return $this->makeFile(
            $this->currentControllerClass,
            HttpConstants::CONTROLLER_SUFFIX,
            $this->sortImports($stub)
        );
    }

    /**
     * 获取模型类地址
     *
     * @param  string  $name
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 1:31 PM
     */
    protected function getQualifyModel(string $name): array
    {
        $name = sprintf(
            '%s\\%s',
            $this->skipBaseNamespaceWithController(),
            $this->getRealName($name, HttpConstants::MODEL_SUFFIX),
        );

        $handleClassName = $this->qualifyModel($name);

        return $this->parseClassNamespaceAndName($handleClassName);
    }

    /**
     * 根据控制器命名空间获取可用的命名空间
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/18 2:43 PM
     */
    protected function skipBaseNamespaceWithController(): string
    {
        $skip = sprintf('%s\\%s\\%s\\', $this->getDefaultNamespaceWithController(), $this->module,
            HttpConstants::PATH_NAME_CONTROLLER);

        return trim(str_replace($skip, '', $this->ctlNamespace), '\\');
    }

    /**
     * 获取控制器类名称
     *
     * @param  string  $baseName
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 10:16 AM
     */
    protected function getControllerRealName(string $baseName): string
    {
        return $this->getRealName($baseName, HttpConstants::CONTROLLER_SUFFIX);
    }
}
