<?php

declare(strict_types = 1);

namespace IDD\Framework\Commands\Make\Curd\Traits;


use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DecimalType;


/**
 * 价格
 * Trait PriceType
 *
 * @package IDD\Framework\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/3/19 13:58
 */
trait PriceType
{
    /**
     * 是否为 金额|价格 字段
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return bool
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 14:06
     */
    protected function isPrice(Column $item): bool
    {
        if (! ($item->getType() instanceof DecimalType)) {
            return false;
        }

        $name = $item->getName();
        foreach ($this->getPriceFieldList() as $value) {
            if (str_ends_with($name, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取 金额|价格 字段后缀
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 14:02
     */
    protected function getPriceFieldList(): array
    {
        return ['price', 'amount', 'money'];
    }
}
