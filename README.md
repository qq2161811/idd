# iDD项目类基础库

## 目录结构
```
idd
├── README.md
├── composer.json
├── config(配置)
│   └── make.php
├── src
│   ├── Commands(脚本命令)
│   │   ├── Make
│   │   │   └── Curd 
│   ├── Contracts(常量文件)
│   ├── Exceptions(异常)
│   ├── Providers(服务提供者)
│   │   ├── Concerns
│   └── Rules(规则验证)
└── tests(测试)
```

## 扩展包地址
```
github: https://github.com/qq2161811/idd
composer: https://packagist.org/packages/maiyuan/idd
```

## 扩展包使用
### 1. 安装扩展包
```
composer require maiyuan/idd 0.1.1
```

### 2. 配置服务提供者
```
在laravel项目中config/app.php配置文件中添加扩展包的服务提供者
'providers' => [
        ...,
        // curd
        \IDD\Framework\Providers\MakeServiceProvider::class,
    ],
```

### 3. 创建配置文件
```
使用curd需要在laravel项目中config文件下创建配置文件make.php
make.php
<?php

declare(strict_types = 1);

return [
	// 默认创建模块(暂无实际使用场景)
	'default_module' => 'Api',

	// 项目模块，一般有api,admin,pc等....
	'modules' => [
		'System',
		'Api',
		'Admin',
	],

	// 项目模块基类列表
	'base_list' => [
		'Admin' => [
			'Controller' => \App\Http\Admin\Controllers\BaseController::class,
			'Services' =>  \App\Http\Admin\Services\BaseService::class,
			'Validate' => \App\Http\Admin\Validates\BaseValidate::class,
		],
		'Api' => [
			'Controller' => \App\Http\Api\Controllers\BaseController::class,
			'Services' =>  \App\Http\Api\Services\BaseService::class,
			'Validate' => \App\Http\Api\Validates\BaseValidate::class,
		],
	],
];
```