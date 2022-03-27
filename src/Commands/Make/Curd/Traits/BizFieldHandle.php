<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


use IDD\Make\Commands\Make\Curd\CurdConstants;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Str;


/**
 * 业务ID处理
 * Trait BizFieldHandle
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:36 AM
 */
trait BizFieldHandle
{
    /**
     * 替换业务ID相关信息
     *
     * @param  string  $stub
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 10:53 AM
     */
    protected function replaceBizField(string $stub): string
    {
        $stub = str_replace(['{{ bizIdFieldNameCamel }}', '{{bizIdFieldNameCamel}}'], $this->getBizIdFieldNameCamel(),
            $stub);
        $stub = str_replace(['{{ bizIdFieldType }}', '{{bizIdFieldType}}'], $this->bizColumnType, $stub);

        return str_replace(['{{ bizFieldName }}', '{{bizFieldName}}'], $this->bizColumnName, $stub);
    }

    /**
     * 获取 bizId 的where语句
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/22 14:02
     */
    protected function getBizIdWhereStub(): string
    {
        return sprintf('where%s', $this->getBizIdFieldNameBigCamel());
    }

    /**
     * 获取 bizId 小驼峰式
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 5:51 PM
     */
    protected function getBizIdFieldNameCamel(): string
    {
        return Str::camel($this->bizColumnName);
    }

    /**
     * 获取 bizId 大驼峰式
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 5:51 PM
     */
    protected function getBizIdFieldNameBigCamel(): string
    {
        return ucfirst(Str::camel($this->bizColumnName));
    }

    /**
     * 初始化业务字段名称
     *
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 4:07 PM
     */
    protected function initBizColumnName(): void
    {
        $bizColumnName = (string) $this->getOptionWithTrim('biz_column');
        if ($bizColumnName) {
            $this->bizColumnName = $bizColumnName;
        } else {
            $this->bizColumnName = CurdConstants::BIZ_ID_FIELD_NAME;
        }
        $this->bizColumnType = CurdConstants::BIZ_ID_FIELD_NAME_TYPE;
    }

    /**
     * 检查业务ID是否使用的主键
     *
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/16 7:40 PM
     */
    protected function isPkKeyWithBizId(): bool
    {
        return $this->currentColumnList[$this->bizColumnName]['autoincrement'];
    }

    /**
     * 是否为 bizId 字段
     *
     * @param  string  $name
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:43 PM
     */
    protected function isBizIdField(string $name): bool
    {
        return str_ends_with($name, 'biz_id') || str_ends_with($name, 'bizId');
    }

    /**
     * 获取YAPI文档
     *
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:01
     */
    protected function getYapiDocWithBizId(): array
    {
        // 使用自增主键
        /** @var \Doctrine\DBAL\Schema\Column $column */
        $column = $this->currentColumnList[$this->bizColumnName]['originalAttribute'];
        if ($this->isPkKeyWithBizId()) {
            return [
                'type'        => 'integer',
                'mock'        => [
                    'mock' => '@integer',
                ],
                'description' => $this->getColumnComment($column),
            ];
        }

        return $this->getYapiDocWithBizIdString($column);
    }

    /**
     * 获取字符串类型的 yapi 文档
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:01
     */
    protected function getYapiDocWithBizIdString(Column $item): array
    {
        $min = 15;
        $max = 20;
        // $max = $this->getStringLength($item);
        // if ($max <= $min) {
        //     $min = 1;
        // }

        return [
            'type'        => 'string',
            'mock'        => [
                'mock' => '@id',
            ],
            'pattern'     => sprintf('^[1-9]{%d,%d}$', $min, $max),
            'minLength'   => $min,
            'maxLength'   => $max,
            'description' => $this->getColumnComment($item),
        ];
    }
}
