<?php

declare(strict_types = 1);

namespace IDD\Make\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;


/**
 * 命令行基类
 * Class BaseCommand
 *
 * @package IDD\Make\Commands
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/24 2:32 PM
 */
abstract class BaseCommand extends Command
{
    /**
     * 时间
     *
     * @var \Illuminate\Support\Carbon|null
     */
    protected ?Carbon $carbon = null;

    /**
     * 开始时间
     *
     * @var \Illuminate\Support\Carbon|null
     */
    protected ?Carbon $start = null;

    /**
     * 总数
     *
     * @var int
     */
    protected int $total = 0;

    /**
     * 成功数量
     *
     * @var int
     */
    protected int $successesNum = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Carbon $carbon)
    {
        parent::__construct();

        $this->carbon = $carbon->copy();
        $this->start  = $carbon->copy();
    }
}
