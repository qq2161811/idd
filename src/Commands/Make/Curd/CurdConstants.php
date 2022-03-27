<?php

declare(strict_types = 1);

namespace IDD\Framework\Commands\Make\Curd;

use IDD\Framework\Contracts\HttpConstants;


/**
 * 常量类
 * Interface CurdConstants
 *
 * @package IDD\Framework\Commands\Curd
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/15 2:26 PM
 */
interface CurdConstants extends HttpConstants
{
    /**
     * 默认业务字段名称
     */
    public const BIZ_ID_FIELD_NAME = 'biz_id';
    /**
     * 默认业务字段名称类型
     */
    public const BIZ_ID_FIELD_NAME_TYPE = 'string';

    /**
     * stubs 文件夹名称
     */
    public const STUBS_DIR = 'stubs';

    /**
     * stubs 列表
     */
    public const STUBS_LIST = [
        self::CONTROLLER_SUFFIX => self::STUBS_DIR.'/controller.stub',
        self::SERVICE_SUFFIX    => self::STUBS_DIR.'/service.stub',
        self::MODEL_SUFFIX      => self::STUBS_DIR.'/model.stub',
        self::VALIDATE_SUFFIX   => self::STUBS_DIR.'/validate.stub',
    ];
}
