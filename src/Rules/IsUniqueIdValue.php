<?php

namespace IDD\Make\Rules;

use Illuminate\Contracts\Validation\Rule;


/**
 * 验证是否为 UniqueId
 *
 * Class IsUniqueId
 *
 * @package IDD\Make\Rules
 * @author  ShuQingZai<overbeck.jack@qq.com> 2021/12/9 9:37 上午
 */
class IsUniqueIdValue implements Rule
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
        return isBizIdValue($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->selfMessage ?: '无效的业务ID';
    }
}
