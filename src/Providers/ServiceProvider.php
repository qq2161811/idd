<?php

declare(strict_types = 1);

namespace IDD\Make\Providers;

use IDD\Make\Providers\Concerns\InteractsWithApplication;
use IDD\Make\Providers\Concerns\HasAssets;
use IDD\Make\Providers\Concerns\HasConfig;
use IDD\Make\Providers\Concerns\HasFactories;
use IDD\Make\Providers\Concerns\HasMigrations;
use IDD\Make\Providers\Concerns\HasTranslations;
use IDD\Make\Providers\Concerns\HasViews;
use IDD\Make\Exceptions\PackageException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Illuminate\Support\Str;
use ReflectionClass;

/**
 * 服务提供者基类
 * Class ServiceProvider
 *
 * @package IDD\Make
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
			(new ReflectionClass($this))->getFileName(), 2
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
	 * @throws \IDD\Make\Exceptions\PackageException
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
	 * @throws \IDD\Make\Exceptions\PackageException
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