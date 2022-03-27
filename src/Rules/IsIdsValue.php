<?php

namespace IDD\Framework\Rules;

use Illuminate\Contracts\Validation\Rule;


/**
 * 是否为 id 集合
 * Class IsIdsValue
 *
 * @package IDD\Framework\Rules
 * @author  ShuQingZai<overbeck.jack@qq.com> 2021/11/19 2:17 下午
 */
class IsIdsValue implements Rule
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
    public function passes($attribute, $value)
    {
        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (is_int($value)) {
            $value = [$value];
        }

        return isIdsValue($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->selfMessage ?: '错误的ID集合';
    }
}
