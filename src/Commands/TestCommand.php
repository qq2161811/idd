<?php

namespace IDD\Make\Commands;

use App\Models\Store;
use App\Services\ShopifyBackup\StoreFrontToken;
use GuzzleHttp\Client;

class TestCommand extends \Illuminate\Console\Command
{

    protected $name = 'test';//命令名称

    protected $description = '测试'; // 命令描述，没什么用

    public function handle()
    {
    }


}
