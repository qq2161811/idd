<?php

declare(strict_types=1);

namespace IDD\Framework\Providers\Concerns;

/**
 *
 *
 * Trait HasTranslations
 *
 * @package IDD\Framework\Providers\Concerns
 * @author  ZhongYu<262815974@qq.com> 2022/3/27 14:48
 */
trait HasTranslations
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get the translations path.
     *
     * @return string
     */
    protected function getTranslationsPath(): string
    {
        return $this->getBasePath().DIRECTORY_SEPARATOR.'translations';
    }

    /**
     * Get the destination views path.
     *
     * @return string
     */
    protected function getTranslationsDestinationPath(): string
    {
        return $this->app['path.lang'].DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.$this->getPackageName();
    }

    /**
     * Publish the translations.
     *
     * @param  string|null  $path
     */
    protected function publishTranslations(?string $path = null): void
    {
        $this->publishes([
            $this->getTranslationsPath() => $path ?: $this->getTranslationsDestinationPath(),
        ], $this->getPublishedTags('translations'));
    }

    /**
     * Load the translations files.
     */
    protected function loadTranslations(): void
    {
        $path = $this->getTranslationsPath();

        $this->loadTranslationsFrom($path, $this->getPackageName());
        $this->loadJsonTranslationsFrom($path);
    }
}
