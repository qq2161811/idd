<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 模板模型
 * Class Model
 * @method static \Illuminate\Database\Eloquent\Builder|CurdModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurdModel newQuery()
 * @method static \Illuminate\Database\Query\Builder|CurdModel onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|CurdModel query()
 * @method static \Illuminate\Database\Query\Builder|CurdModel withTrashed()
 * @method static \Illuminate\Database\Query\Builder|CurdModel withoutTrashed()
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class CurdModel extends Model
{
    use SoftDeletes;
}
