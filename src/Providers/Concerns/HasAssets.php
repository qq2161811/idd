<?php

declare(strict_types=1);

namespace IDD\Framework\Providers\Concerns;

/**
 *
 *
 * Trait HasAssets
 *
 * @package IDD\Framework\Providers\Concerns
 * @author  ZhongYu<262815974@qq.com> 2022/3/27 14:48
 */
trait HasAssets
{
    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Get the assets path.
     *
     * @return string
     */
    protected function getAssetsFolder(): string
    {
        return realpath($this->getBasePath().DIRECTORY_SEPARATOR.'assets');
    }

    /**
     * Get the assets destination path.
     *
     * @return string
     */
    protected function assetsDestinationPath(): string
    {
        return base_path('assets'.DIRECTORY_SEPARATOR.$this->getPackageName());
    }

    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Publish the assets.
     */
    protected function publishAssets(): void
    {
        $this->publishes([
            $this->getAssetsFolder() => $this->assetsDestinationPath(),
        ], $this->getPublishedTags('assets'));
    }
}
