<?php

declare(strict_types = 1);

namespace Maiyuan\IDD;

use Illuminate\Support\ServiceProvider;

class IDDServiceProvider extends ServiceProvider
{
	public function boot()
	{
		//
	}

	public function register()
	{
		$this->app->singleton('iDD', function () {
			return new IDD();
		});
	}
}