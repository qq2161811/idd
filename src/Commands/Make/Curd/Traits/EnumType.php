<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BooleanType;

/**
 * 枚举值处理
 * Trait Enum
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/3/19 11:50
 */
trait EnumType
{
    /**
     * 检查是否当作枚举类处理
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 11:35
     */
    protected function isEnum(Column $item): bool
    {
        return ! empty($this->getEnumValue($item));
    }

    /**
     * 获取枚举值
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return int[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 11:53
     */
    protected function getEnumValue(Column $item): array
    {
        if (! ($item->getType() instanceof BooleanType)) {
            // tinyint 视为 枚举类型，其他类型暂不考虑
            return [];
        }
        // 注释以 英文冒号(:) 分割名称与值，每个值以 英文逗号(,) 分割，每个值注释与值以 = 号分割
        // 枚举值必须有两个或以上值
        // 示例: 状态:0=禁用,1=正常,2=隐藏
        $comment = $this->getColumnComment($item);
        $enum    = explode(':', $comment);
        if (! is_array($enum) || (count($enum) !== 2)) {
            return [];
        }
        $enumItem = explode(',', $enum[1]);
        if (! $enumItem) {
            return [];
        }
        $values = [];
        foreach ($enumItem as $value) {
            $valList = explode('=', $value);
            if (! is_array($valList) || (count($valList) !== 2)) {
                $values = [];
                break;
            }
            $values[] = (int) $valList[0];
        }
        if ($values) {
            $uqValues = array_values(array_unique($values));
            $values   = count($uqValues) > 1 ? $uqValues : [];
        }

        return $values;
    }
}
