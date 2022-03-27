<?php

namespace IDD\Make\Commands;

use App\Http\System\Service\System\Pending;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Str;
use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Laravel\Horizon\MasterSupervisor;

/**
 * 检查队列是否存在pending异常
 *
 * Class CheckQueuePending
 *
 * @package IDD\Make\Commands
 * @author  ZhongYu 2021/11/9 6:03 下午
 */
class CheckQueuePending extends Command
{
    use InteractsWithTime;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:check-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '检查pending异常';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): int
    {
        app(Pending::class)->checkQueuePending();

        return 0;
    }
}
