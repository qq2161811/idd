<?php

declare(strict_types=1);

namespace IDD\Framework\Providers;

use IDD\Framework\Commands\Make\Curd\Generator;

/**
 * curd服务提供者
 * Class MakeServiceProvider
 *
 * @package IDD\Framework
 * @author  ZhongYu<262815974@qq.com> 2022/3/27 1:40
 */
class MakeServiceProvider extends ServiceProvider
{
	/* -----------------------------------------------------------------
	 |  Properties
	 | -----------------------------------------------------------------
	 */

	/**
	 * Package name.
	 *
	 * @var string|null
	 */
	protected ?string $package = 'make';

	/* -----------------------------------------------------------------
	 |  Main Methods
	 | -----------------------------------------------------------------
	 */

	/**
	 * Register the service provider.
	 *
	 * @throws \IDD\Framework\Exceptions\PackageException
	 * @author ZhongYu<262815974@qq.com> 2022/3/27 1:56
	 */
	public function register(): void
	{
		parent::register();

		$this->registerConfig();

		$this->registerCommands([
			Generator::class,
		]);
	}

	/**
	 * Boot the service provider.
	 */
	public function boot(): void
	{
		$this->loadTranslations();
		$this->loadViews();

		if ($this->app->runningInConsole()) {
			$this->publishConfig();
			$this->publishTranslations();
			$this->publishViews();
		}
	}
}
