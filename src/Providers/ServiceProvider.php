<?php

declare(strict_types = 1);

namespace IDD\Framework\Providers;

use IDD\Framework\Providers\Concerns\InteractsWithApplication;
use IDD\Framework\Providers\Concerns\HasAssets;
use IDD\Framework\Providers\Concerns\HasConfig;
use IDD\Framework\Providers\Concerns\HasFactories;
use IDD\Framework\Providers\Concerns\HasMigrations;
use IDD\Framework\Providers\Concerns\HasTranslations;
use IDD\Framework\Providers\Concerns\HasViews;
use IDD\Framework\Exceptions\PackageException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * 服务提供者基类
 * Class ServiceProvider
 *
 * @package IDD\Framework
 * @author  ZhongYu<262815974@qq.com> 2022/3/27 0:42
 */
abstract class ServiceProvider extends IlluminateServiceProvider
{
	/* -----------------------------------------------------------------
	 |  Traits
	 | -----------------------------------------------------------------
	 */

	use HasAssets,
		HasConfig,
		HasFactories,
		HasMigrations,
		HasTranslations,
		HasViews,
		InteractsWithApplication;

	/* -----------------------------------------------------------------
	 |  Properties
	 | -----------------------------------------------------------------
	 */

	/**
	 * Vendor name.
	 *
	 * @var string
	 */
	protected string $vendor = 'idd';

	/**
	 * Package name.
	 *
	 * @var string|null
	 */
	protected ?string $package;

	/**
	 * Package base path.
	 *
	 * @var string
	 */
	protected string $basePath;

	/* -----------------------------------------------------------------
     |  Constructor
     | -----------------------------------------------------------------
     */

	/**
	 * Create a new service provider instance.
	 *
	 * @param  \Illuminate\Contracts\Foundation\Application  $app
	 */
	public function __construct(Application $app)
	{
		parent::__construct($app);

		$this->basePath = $this->resolveBasePath();
	}

	/**
	 * Resolve the base path of the package.
	 *
	 * @return string
	 */
	protected function resolveBasePath(): string
	{
		return dirname(
			(new ReflectionClass($this))->getFileName(), 3
		);
	}

	/* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

	/**
	 * Get the base path of the package.
	 *
	 * @return string
	 */
	public function getBasePath(): string
	{
		return $this->basePath;
	}

	/**
	 * Get the vendor name.
	 *
	 * @return string
	 */
	protected function getVendorName(): string
	{
		return $this->vendor;
	}

	/**
	 * Get the package name.
	 *
	 * @return string|null
	 */
	protected function getPackageName(): ?string
	{
		return $this->package;
	}

	/* -----------------------------------------------------------------
	 |  Main Methods
	 | -----------------------------------------------------------------
	 */

	/**
	 * Register the service provider.
	 *
	 * @throws \IDD\Framework\Exceptions\PackageException
	 * @author ZhongYu<262815974@qq.com> 2022/3/27 1:55
	 */
	public function register(): void
	{
		parent::register();

		$this->checkPackageName();
	}

	/* -----------------------------------------------------------------
	 |  Package Methods
	 | -----------------------------------------------------------------
	 */

	/**
	 * Publish all the package files.
	 */
	protected function publishAll(): void
	{
		$this->publishAssets();
		$this->publishConfig();
		$this->publishFactories();
		$this->publishMigrations();
		$this->publishTranslations();
		$this->publishViews();
	}

	/* -----------------------------------------------------------------
	 |  Check Methods
	 | -----------------------------------------------------------------
	 */

	/**
	 * Check package name.
	 *
	 * @throws \IDD\Framework\Exceptions\PackageException
	 */
	protected function checkPackageName(): void
	{
		if (empty($this->getVendorName()) || empty($this->getPackageName())) {
			throw PackageException::unspecifiedName();
		}
	}

	/* -----------------------------------------------------------------
	 |  Other Methods
	 | -----------------------------------------------------------------
	 */

	/**
	 * Get the published tags.
	 *
	 * @param  string  $tag
	 * @return array
	 */
	protected function getPublishedTags(string $tag): array
	{
		$package = $this->getPackageName();

		return array_map(static function ($name) {
			return Str::slug($name);
		}, [$this->getVendorName(), $package, $tag, $package.'-'.$tag]);
	}
}