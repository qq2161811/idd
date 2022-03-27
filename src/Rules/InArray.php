<?php

namespace IDD\Make\Rules;

use Illuminate\Contracts\Validation\Rule;


/**
 * 验证是否存在于 array 中 - 强类型检查
 * Class InArray
 *
 * @package IDD\Make\Rules
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/3/3 4:08 PM
 */
class InArray implements Rule
{
    /**
     * 需要验证的数组
     *
     * @var array
     */
    protected array $values = [];

    /**
     * 自定义错误提示语
     *
     * @var string
     */
    protected string $message = '';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $values, string $message = '')
    {
        $this->values  = $values;
        $this->message = $message;
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
        return ! empty($this->values) && in_array($value, $this->values, true);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message ?: '参数错误';
    }
}
