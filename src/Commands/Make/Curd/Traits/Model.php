<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


use IDD\Make\HttpConstants;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\PhpDateTimeMappingType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;


/**
 * 模型相关
 * Trait Model
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:07 AM
 */
trait Model
{
    /**
     * 创建 model 文件
     *
     * @param  string  $name
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 2:16 PM
     */
    protected function makeModel(string $name): bool
    {
        [$modelName, $modelClass] = $this->getQualifyModel($name);
        [$softDelName, $softDelNamespace] = $this->getSoftDeleteStub();
        $stub = $this->getStub(HttpConstants::MODEL_SUFFIX);
        $stub = $this->replaceNamespace($stub, $modelClass)->replaceClass($stub, $modelName);
        $stub = $this->replaceDescription($stub, $this->getClassComment('模型'));
        $stub = str_replace(['{{ propertyDocs }}', '{{propertyDocs}}'], $this->getPropertyDocsStub(), $stub);
        $stub = str_replace(['{{ fillableList }}', '{{fillableList}}'], $this->getModelFillableStub(), $stub);
        $stub = str_replace(['{{ castsList }}', '{{castsList}}'], $this->getModelCastsStub(), $stub);
        $stub = str_replace(['{{ tableName }}', '{{tableName}}'], $this->getTableShortName(), $stub);
        $stub = str_replace(['{{ softDelName }}', '{{softDelName}}'], $softDelName, $stub);
        $stub = str_replace(['{{ softDelNamespace }}', '{{softDelNamespace}}'], $softDelNamespace, $stub);
        $stub = str_replace(['{{ defaultValues }}', '{{defaultValues}}'], $this->getDefaultValuesStub(), $stub);
        $this->getDefaultValuesStub();
        $res = $this->makeFile($modelClass, HttpConstants::MODEL_SUFFIX, $this->sortImports($stub));
        if ($res) {
            // 生成属性条件方法
            Artisan::call('ide-helper:models', [
                'model' => [$modelClass], '--write' => true,
            ]);
        }

        return $res;
    }


    /**
     * 获取模型 fillable
     *
     * @param  string  $glueSad
     * @param  array   $except  自定义跳过的字段
     * @return array{ 0: string[], 1: string }
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 4:52 PM
     */
    protected function getModelFillable(string $glueSad, array $except = []): array
    {
        $casts = array_column($this->currentColumnList, 'cast', 'name');
        $casts = Arr::except(
            $casts,
            [...$this->getModelDefaultTimeColumn(), ...$this->currentTable->getPrimaryKey()->getColumns(), ...$except]
        );
        if (empty($casts)) {
            return [[], ''];
        }
        $fillable    = array_keys($casts);
        $fillableStr = $this->arrayToStrWithNumber($fillable, $glueSad);

        return [$fillable, $fillableStr];
    }

    /**
     * 获取模型属性注释
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/17 12:50 PM
     */
    protected function getPropertyDocsStub(): string
    {
        $maxKeyLen  = 0;
        $maxTypeLen = 0;
        $count      = 0;
        $columns    = $this->currentColumnList;
        foreach ($columns as &$item) {
            ++$count;
            $phpDoc                  = $item['phpDoc'];
            $item['phpDocTypeStart'] = sprintf(' * @property %s ', $phpDoc['type']);
            $typeKeyLen              = strlen($item['phpDocTypeStart']);
            if ($typeKeyLen > $maxTypeLen) {
                $maxTypeLen = $typeKeyLen;
            }
            $item['phpDocTypeLen'] = $typeKeyLen;
            if ($item['nameLen'] > $maxKeyLen) {
                $maxKeyLen = $item['nameLen'];
            }
        }
        unset($item);
        $docs = '';
        $key  = 0;
        // $str  = '...请自行查阅数据表';
        foreach ($columns as $value) {
            ++$key;
            $doc = sprintf(
                '%s%s$%s%s%s',
                $value['phpDocTypeStart'],
                $this->getStrIndent($maxTypeLen - $value['phpDocTypeLen']),
                $value['phpDoc']['name'],
                $this->getStrIndent($maxKeyLen - $value['nameLen'] + 1),
                $value['phpDoc']['desc']
            );
            // $docs .= strCut($doc, 100, $str).($count === $key ? '' : PHP_EOL);
            $docs .= $doc.($count === $key ? '' : PHP_EOL);
        }

        return <<<EOD

 *
$docs
EOD;
    }

    /**
     * 获取模型 fillable 模板
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 3:04 PM
     */
    protected function getModelFillableStub(): string
    {
        [, $fillableStr] = $this->getModelFillable($this->getStrIndent());

        return <<<EOD


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected \$fillable = [
$fillableStr
    ];
EOD;
    }

    /**
     * 获取模型类型转换casts
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 3:04 PM
     */
    protected function getModelCastsStub(): string
    {
        $casts = array_column($this->currentColumnList, 'cast', 'name');
        $casts = Arr::except($casts, $this->getModelDefaultTimeColumn());
        if (empty($casts)) {
            return '';
        }

        $castsStr = $this->arrayToString($casts);
        $castsStr = str_replace('datetime::class', "'datetime'", $castsStr);

        return <<<EOD


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected \$casts = [
$castsStr
    ];
EOD;
    }

    /**
     * 获取软删除模板
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 2:36 PM
     */
    protected function getSoftDeleteStub(): array
    {
        $deletedAt = $this->curdModel->getDeletedAtColumn();
        if (! isset($this->currentColumnList[$deletedAt])) {
            return ['', ''];
        }
        $softDelNamespace = <<<EOD

use Illuminate\Database\Eloquent\SoftDeletes;
EOD;
        $softDelName      = <<<EOD

    use SoftDeletes;

EOD;

        return [$softDelName, $softDelNamespace];
    }

    /**
     * 获取默认值stub
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/21 22:02
     */
    protected function getDefaultValuesStub(): string
    {
        [$fillable,] = $this->getModelFillable($this->getStrIndent());
        $modelDefValues = array_column($this->currentColumnList, 'modelDefValue', 'name');
        $modelDefValues = Arr::only($modelDefValues, $fillable);
        if (empty($modelDefValues)) {
            return '';
        }
        $valuesStr = $this->arrayToString($modelDefValues, 12);

        return <<<EOD


    /**
     * @inheritDoc
     */
    public function getDefaultValues(): array
    {
        return [
$valuesStr
        ];
    }
EOD;
    }


    /**
     * 获取模型默认的时间字段
     *
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 2:55 PM
     */
    protected function getModelDefaultTimeColumn(): array
    {
        return [
            $this->curdModel->getCreatedAtColumn(),
            $this->curdModel->getUpdatedAtColumn(),
            $this->curdModel->getDeletedAtColumn(),
        ];
    }

    /**
     * 获取模型默认值
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return float|int|string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/21 21:10
     */
    protected function getModelDefaultValue(Column $item)
    {
        $default = $item->getDefault();
        if (is_null($default) || str_starts_with((string) $default, 'CURRENT_TIMESTAMP')) {
            $default = null;
        }
        $type = $item->getType();
        switch (true) {
            case $this->isEnum($item): // 枚举类型
                $enumValue = $this->getEnumValue($item);
                $value     = ! is_null($default) ? (int) $default : $enumValue[0];
                break;
            case $this->isIntType($type): // 整型
                $value = ! is_null($default) ? (int) $default : 0;

                break;
            case $this->isPrice($item): // 价格字段
                $value = ! is_null($default) ? (string) $default : '0.00';

                break;
            case $type instanceof FloatType:
                $value = ! is_null($default) ? (float) $default : 0.0;

                break;
            case $type instanceof DateType:
            case $type instanceof PhpDateTimeMappingType:
                $value = ! is_null($default) ? $default : '\Illuminate\Support\Carbon::now()->toDateTimeLocalString()';

                break;
            case $type instanceof JsonType:
                $value = ! is_null($default) ? $default : '[]';

                break;
            default:
                $value = ! is_null($default) ? $default : '';

                break;
        }

        return $value;
    }
}
