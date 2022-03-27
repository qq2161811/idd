<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


/**
 * 作者信息
 * Trait Author
 *
 * @package IDD\Make\Commands\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/15 10:37 AM
 */
trait Author
{
    /**
     * 获取作者信息与当前时间
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 10:41 AM
     */
    protected function getAuthorAndTime(): string
    {
        return $this->getAuthor().' '.date('Y/m/d h:i A');
    }

    /**
     * 获取作者
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/15 6:02 PM
     */
    protected function getAuthor(): string
    {
        $name = env('IDE_AUTHOR');
        if ($name) {
            return $name;
        }

        if (function_exists('exec')) {
            $username = exec('git config user.name');
            $email    = exec('git config user.email');

            return $username.'<'.$email.'>';
        }

        return env('APP_DEVELOPER', 'LaravelArtisan');
    }
}
