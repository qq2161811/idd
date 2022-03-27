<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\PhpDateTimeMappingType;
use Doctrine\DBAL\Types\PhpIntegerMappingType;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Doctrine\DBAL\Types\FloatType;


/**
 * 数据表操作
 * Trait Table
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:28 AM
 */
trait CurrentTableHandle
{
    /**
     * 获取当前数据表名称 -- 小驼峰式
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/17 10:43 AM
     */
    protected function getTableShortNameCamel(): string
    {
        return Str::studly($this->getTableShortName());
    }

    /**
     * 获取当前数据表名称
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/17 10:47 AM
     */
    protected function getTableShortName(): string
    {
        return $this->currentTable->getShortestName($this->currentTable->getNamespaceName());
    }

    /**
     * 获取主键 -- 第一个主键
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/17 11:06 AM
     */
    protected function getPrimaryKey(): string
    {
        $pkKeys = collect($this->currentColumnList)->where('primaryKey', true)->toArray();
        $pkKey  = array_values($pkKeys)[0]['name'] ?? null;
        if (empty($pkKey)) {
            throw new \RuntimeException('Failed to get the primaryKey.');
        }

        return $pkKey;
    }

    /**
     * 获取主键信息 -- 第一个主键
     *
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/17 11:07 AM
     */
    protected function getPrimaryKeyInfo(): array
    {
        $pkKey = $this->getPrimaryKey();

        return $this->currentColumnList[$pkKey];
    }

    /**
     * 格式化数据表字段
     *
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 4:12 PM
     */
    protected function formatTableColumns(): void
    {
        $primaryKeyIndex = $this->currentTable->getPrimaryKey();
        if (is_null($primaryKeyIndex)) {
            throw new \RuntimeException('The table "'.$this->getTableShortName().'" must have at least one primary key index.');
        }
        $primaryKeyColumns = $primaryKeyIndex->getColumns();
        $primaryKey        = $primaryKeyColumns[0];
        $tableColumns      = $this->currentTable->getColumns();
        // 如果没有业务ID，这修改为主键
        if (empty($this->bizColumnName) || ! isset($tableColumns[$this->bizColumnName])) {
            $this->bizColumnName = $primaryKey;
        }
        $columnList = [];
        foreach ($tableColumns as $key => $item) {
            $column                      = $item->toArray();
            $column['originalAttribute'] = $item;
            $column['nameLen']           = strlen($item->getName());
            $column['nameCamel']         = Str::camel($item->getName());
            $column['phpType']           = $this->formatPropertyType($item->getType());
            $column['cast']              = $this->formatLaravelCast($item->getType());
            // 业务ID
            $column['isBiz'] = false;
            if ($key === $this->bizColumnName) {
                $column['isBiz'] = true;
                if ($this->isIntType($item->getType())) {
                    $this->bizColumnType = 'int';
                } else {
                    $this->bizColumnType = 'string';
                }
            }

            // 主键
            $column['primaryKey'] = false;
            if ($key === $primaryKey) {
                $column['primaryKey'] = true;
            }

            // 验证器信息
            $column['validates'] = $this->createValidateRule($item);

            // 注释信息
            $phpDocType = $column['phpType'];
            if ($item->getType() instanceof PhpDateTimeMappingType) {
                // 日期类型，允许直接传入 日期格式 string
                $phpDocType .= '|string';
            }
            if (! $item->getNotnull() && ! in_array($item->getName(), $primaryKeyColumns, true)) {
                // 非主键并且允许 null
                $phpDocType .= '|null';
            }
            $phpDocDesc       = $this->getColumnComment($item);
            $column['phpDoc'] = [
                'type' => $phpDocType,
                'name' => $item->getName(),
                'desc' => $phpDocDesc,
            ];

            // YAPI 字段文档信息
            $column['yapiDoc'] = $this->createJsonSchema($item);

            // 模型默认值
            $column['modelDefValue'] = $this->getModelDefaultValue($item);

            $columnList[] = $column;
        }

        $this->currentColumnList = array_column($columnList, null, 'name');
    }

    /**
     * 转换数据库类型为php类型
     *
     * @param  \Doctrine\DBAL\Types\Type  $type
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 5:19 PM
     */
    protected function formatPropertyType(Type $type): string
    {
        switch (true) {
            case $type instanceof BooleanType:
            case $this->isIntType($type):
                $typeName = 'int';
                break;
            case $type instanceof FloatType:
                $typeName = 'float';
                break;
            case $type instanceof DateType:
            case $type instanceof PhpDateTimeMappingType:
                $typeName = '\\'.Carbon::class;
                break;
            case $type instanceof JsonType:
                $typeName = 'array';
                break;
            default:
                $typeName = 'string';
                break;
        }

        return $typeName;
    }

    /**
     * 格式化laravel cast模型类型转换
     *
     * @param  \Doctrine\DBAL\Types\Type  $type
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 2:13 PM
     */
    protected function formatLaravelCast(Type $type): string
    {
        switch (true) {
            case $type instanceof BooleanType:
            case $this->isIntType($type):
                $typeName = 'int';
                break;
            case $type instanceof FloatType :
                $typeName = 'float';
                break;
            case $type instanceof DateType:
                $typeName = 'date';
                break;
            case $type instanceof PhpDateTimeMappingType:
                $typeName = 'datetime';
                break;
            case $type instanceof JsonType:
                $typeName = 'array';
                break;
            default:
                $typeName = 'string';
                break;
        }

        return $typeName;
    }

    /**
     * 检测字段类型是否为 int 排除 bigint
     *
     * @param  \Doctrine\DBAL\Types\Type  $type
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/18 6:22 PM
     */
    protected function isIntType(Type $type): bool
    {
        return ($type instanceof PhpIntegerMappingType) && ! ($type instanceof BigIntType);
    }

    /**
     * 获取类名注释
     *
     * @param  string  $append  追加注释
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 6:18 PM
     */
    protected function getClassComment(string $append = ''): string
    {
        $comment = $this->currentTable->getComment() ?? '';
        if (str_ends_with($comment, '表')) {
            $comment = mb_substr($comment, 0, -1);
        }
        if ($append && $comment && ! str_ends_with($comment, $append)) {
            $comment .= (' --- '.$append);
        }

        return $comment;
    }

    /**
     * 获取并解析输入的数据表名称
     *
     * @return array
     */
    protected function getTableNameListInput(): array
    {
        $tables                   = trim($this->argument('tables'));
        $this->inputTableNameList = $tables ? (array) explode(',', $tables) : [];

        return $this->inputTableNameList;
    }

    /**
     * 获取字段注释
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 1:58 PM
     */
    protected function getColumnComment(Column $item): string
    {
        $comment = $item->getComment();
        if (is_null($comment)) {
            $comment = $item->getName();
        }

        return $comment;
    }

    /**
     * 获取字段注释可读名称
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:39 PM
     */
    protected function getCommentName(Column $item): string
    {
        $comment = $this->getColumnComment($item);

        return explode(':', $comment)[0] ?? $comment;
    }
}
