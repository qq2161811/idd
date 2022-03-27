<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


use Doctrine\DBAL\Schema\Column;

/**
 * 字段
 * Trait FieldHandle
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/3/19 13:40
 */
trait FieldHandle
{
    /**
     * 是否为 ID 字段
     *
     * @param  string  $name
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:43 PM
     */
    protected function isIdField(string $name): bool
    {
        return str_ends_with($name, 'id');
    }

    /**
     * 转换数字为浮点
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @param  bool                          $toFloat
     * @return float|string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/23 19:51
     */
    protected function getNumberWithFloat(Column $item, bool $toFloat)
    {
        $res = sprintf('%s.%s', $this->getStrIndent($item->getPrecision() - $item->getScale(), '9'),
            $this->getStrIndent($item->getScale(), '9'));
        if ($toFloat) {
            return round((float) $res, $item->getScale());
        }

        return $res;
    }
}
