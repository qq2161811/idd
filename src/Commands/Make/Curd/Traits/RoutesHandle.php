<?php

declare(strict_types = 1);


namespace IDD\Framework\Commands\Make\Curd\Traits;


/**
 * 路由表处理
 * Trait RoutesHandle
 *
 * @package IDD\Framework\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:38 AM
 */
trait RoutesHandle
{
    /**
     * 获取路由
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/18 10:03 AM
     */
    protected function getRoutes(): string
    {
        // $groupName    = str_replace('_', '-', $this->getTableShortName());
        $groupName    = lcfirst($this->getTableShortNameCamel());
        $class        = sprintf('\\%s::class', $this->currentControllerClass);
        $classComment = $this->getClassComment('路由表');
        $bizIdKey     = sprintf('{%s}', $this->getBizIdFieldNameCamel());

        return <<<EOD
    // $classComment
    Route::prefix('$groupName')->group(function () {
        // 创建
        Route::post('/', [$class, 'create']);
        // 更新
        Route::post('/$bizIdKey/update', [$class, 'update']);
        // 详情
        Route::get('/$bizIdKey', [$class, 'detail']);
        // 删除
        Route::post('/delete', [$class, 'delete']);
        // 列表
        Route::get('/', [$class, 'index']);
    });
EOD;
    }

}
