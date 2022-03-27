<?php

declare(strict_types = 1);

namespace IDD\Framework\Contracts;

/**
 * http 文件相关常量
 * Interface HttpConstants
 *
 * @package IDD\Framework\Contracts
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/15 1:51 PM
 */
interface HttpConstants
{
	/**
	 * 控制器包名
	 */
	public const PATH_NAME_CONTROLLER = 'Controllers';

	/**
	 * 验证器包名
	 */
	public const PATH_NAME_VALIDATE = 'Validates';

	/**
	 * 服务包名
	 */
	public const PATH_NAME_SERVICE = 'Services';

	/**
	 * 模型包名
	 */
	public const PATH_NAME_MODEL = 'Models';

	/**
	 * 控制器类后缀
	 */
	public const CONTROLLER_SUFFIX = 'Controller';

	/**
	 * 服务类后缀
	 */
	public const SERVICE_SUFFIX = 'Services';

	/**
	 * 验证类后缀
	 */
	public const VALIDATE_SUFFIX = 'Validate';

	/**
	 * 模型类后缀
	 */
	public const MODEL_SUFFIX = 'Model';

	/**
	 * 应用项目模块 PC System Client System
	 */
	public const MODULES_SYSTEM = 'System';

	/**
	 * 应用项目模块 PC System Client Api
	 */
	public const MODULES_API = 'Api';
	/**
	 * 应用项目模块 PC System Client Admin
	 */
	public const MODULES_ADMIN = 'Admin';

	/**
	 * 应用项目模块 PC System Client Api
	 */
	public const MODULES_LIST = [
		self::MODULES_SYSTEM,
		self::MODULES_API,
		self::MODULES_ADMIN,
	];
}