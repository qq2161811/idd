<?php

declare(strict_types = 1);

namespace IDD\Framework\Exceptions;

use Exception;

/**
 *
 *
 * Class PackageException
 *
 * @package IDD\Framework
 * @author  ZhongYu<262815974@qq.com> 2022/3/27 1:50
 */
class PackageException extends Exception
{
	public static function unspecifiedName(): self
	{
		return new static('You must specify the vendor/package name.');
	}
}