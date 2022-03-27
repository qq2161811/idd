<?php

declare(strict_types=1);

namespace IDD\Framework\Commands;

use Illuminate\Console\Command;
use App\Services\DataStat\MainPicture\MainPicture;

class MainPictureStatistics extends Command
{

    protected $name = 'main_picture:statistics'; //命令名称

    protected $description = '统计每天生成的主图数'; // 命令描述

    protected $signature = 'main_picture:statistics {--date=} {--days=}';

    public function handle()
    {
        
        $days = $this->option('days');
        if ( $days ) {
            for ($i = 1;$i <= $days;$i++) {
                $date = date('Y-m-d', strtotime('-'.$i.' day'));
                app(MainPicture::class)->statCreate($date);
            }
            $this->info('success');
        } else {
            $date = $this->option('date') ?: date('Y-m-d', strtotime('-1 day'));
            $checkDate = $this->checkDateFormat($date);
            if ( !$checkDate || $date > date('Y-m-d', strtotime('-1 day')) ) {
                $this->error('error:日期格式错误');
            } else {
                app(MainPicture::class)->statCreate($date);
                $this->info('success');
            }
        }
    }

    // 校验日期格式
    private function checkDateFormat($date)
    {
        //匹配日期格式
        if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
            //检测是否为日期
            if (checkdate((int)$parts[2], (int)$parts[3], (int)$parts[1]))
                return true;
            else
                return false;
        } else {
            return false;
        }
    }
}
