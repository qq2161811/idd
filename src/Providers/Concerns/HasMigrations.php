<?php

declare(strict_types=1);

namespace IDD\Framework\Providers\Concerns;

/**
 *
 *
 * Trait HasMigrations
 *
 * @package IDD\Framework\Providers\Concerns
 * @author  ZhongYu<262815974@qq.com> 2022/3/27 14:48
 */
trait HasMigrations
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the migrations path.
     *
     * @return string
     */
    protected function getMigrationsPath(): string
    {
        return $this->getBasePath().DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'migrations';
    }

    /**
     * Publish the migration files.
     *
     * @param  string|null  $path
     */
    protected function publishMigrations(?string $path = null): void
    {
        $this->publishes([
            $this->getMigrationsPath() => $path ?: database_path('migrations')
        ], $this->getPublishedTags('migrations'));
    }

    /**
     * Load the migrations files.
     */
    protected function loadMigrations(): void
    {
        $this->loadMigrationsFrom($this->getMigrationsPath());
    }
}
