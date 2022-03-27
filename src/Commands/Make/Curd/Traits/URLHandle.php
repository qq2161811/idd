<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


/**
 * URL 链接地址
 * Trait URLHandle
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/3/19 14:07
 */
trait URLHandle
{

    /**
     * 获取 URL 字段后缀
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:27 PM
     */
    protected function getURLFieldList(): array
    {
        return ['url', 'link',];
    }

    /**
     * 获取 图片 字段后缀
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 3:03 PM
     */
    protected function getImageFieldList(): array
    {
        return ['pic', 'image', 'picture', 'photo'];
    }

    /**
     * 获取 文件 字段后缀
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 3:04 PM
     */
    protected function getFileFieldList(): array
    {
        return ['file'];
    }

    /**
     * 检测是否为 指定类型的URL 字段
     *
     * @param  string  $name
     * @param  string  $type
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:29 PM
     */
    protected function isURLField(string $name, string $type = 'url'): bool
    {
        $urlList  = $this->getURLFieldList();
        $typeList = [
            'url'   => $this->getURLFieldList(),
            'image' => $this->getImageFieldList(),
            'file'  => $this->getFileFieldList(),
        ];
        $list     = $typeList[$type] ?? $urlList;

        foreach ($list as $item) {
            if (str_ends_with($name, $item)) {
                return true;
            }
        }

        return false;
    }
}
