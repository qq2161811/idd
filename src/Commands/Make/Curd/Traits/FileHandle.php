<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;

use IDD\Make\Commands\Make\Curd\CurdConstants;
use Doctrine\DBAL\Schema\Column;


/**
 * 文件处理
 * Trait FileHandle
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:23 AM
 */
trait FileHandle
{
    /**
     * 根据 class 创建文件
     *
     * @param  string  $class
     * @param  string  $type
     * @param  string  $contents
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 1:44 PM
     */
    protected function makeFile(string $class, string $type, string $contents): bool
    {
        // 解析文件路径
        $path = $this->getPath($class);

        return $this->createFile($path, $type, $contents);
    }

    /**
     * 创建文件
     *
     * @param  string  $path      路径
     * @param  string  $type      类型
     * @param  string  $contents  文件内存
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 14:29
     */
    protected function createFile(string $path, string $type, string $contents): bool
    {
        // 检查文件是否已存在，如果存在，并且不选择强制创建，则跳过
        if (file_exists($path) && (! $this->hasOption('force') ||
                ! $this->getOptionWithTrim('force'))) {
            $this->error($type.' "'.$path.'" already exists!');

            return false;
        }
        // 创建文件夹与文件
        $this->makeDirectory($path);
        $this->files->put($path, $contents);
        // 记录已创建的文件
        if (isset($this->createdFileList[$type])) {
            $this->createdFileList[$type][] = $path;
        } else {
            $this->createdFileList[$type] = [$path];
        }

        return true;
    }

    /**
     * 获取 stub
     *
     * @param  string  $type
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 2:31 PM
     */
    protected function getStub(string $type): string
    {
        if (! isset(CurdConstants::STUBS_LIST[$type])) {
            return '';
        }
        $stubFile = $this->getPwd().DIRECTORY_SEPARATOR.trim(CurdConstants::STUBS_LIST[$type], DIRECTORY_SEPARATOR);

        return $this->files->get($stubFile);
    }

    /**
     * 获取文件的完整名称
     *
     * @param  string  $name
     * @param  string  $suffix
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 2:15 PM
     */
    protected function getRealName(string $name, string $suffix): string
    {
        return $this->isUseShortName() ? $name.$suffix : $name;
    }

    /**
     * 获取字符串类型长度
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return int
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 15:52
     */
    protected function getStringLength(Column $item): int
    {
        $len = $item->getLength() ?? 255;

        return 1 > $len ? 255 : $len;
    }
}
