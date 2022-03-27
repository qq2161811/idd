<?php

declare(strict_types = 1);

namespace IDD\Framework\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 是否为价格
 *
 * Class IsPrice
 *
 * @package IDD\Framework\Rules
 * @author  ZhongYu 2022/3/16 7:10 PM
 */
class IsPrice implements Rule
{
    /**
     * 自定义错误提示语
     *
     * @var string
     */
    protected string $selfMessage = '';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(string $message = '')
    {
        $this->selfMessage = $message;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return isPrice($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->selfMessage ?: '错误的金额';
    }
}
