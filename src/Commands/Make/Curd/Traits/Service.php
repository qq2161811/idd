<?php

declare(strict_types = 1);

namespace IDD\Framework\Commands\Make\Curd\Traits;


use IDD\Framework\Contracts\HttpConstants;


/**
 * 服务相关
 * Trait Service
 *
 * @package IDD\Framework\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:09 AM
 */
trait Service
{
    /**
     * 创建 service 文件
     *
     * @param  string  $name
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 11:06 AM
     */
    protected function makeService(string $name): bool
    {
        [$className, $classNamespace] = $this->parseClass(
            $name,
            HttpConstants::PATH_NAME_SERVICE,
            HttpConstants::SERVICE_SUFFIX
        );

        [$modelName, $modelClass] = $this->getQualifyModel($name);
        // 名称一致，需要添加别名
        if ($modelName === $className) {
            $handleModel    = $modelName.HttpConstants::MODEL_SUFFIX;
            $modelNamespace = $modelClass.' as '.$handleModel;
        } else {
            $handleModel    = $modelName;
            $modelNamespace = $modelClass;
        }

        $stub = $this->getStub(HttpConstants::SERVICE_SUFFIX);
        $stub = $this->replaceNamespace($stub, $classNamespace)->replaceClass($stub, $className);
        $stub = $this->replaceDescription($stub, $this->getClassComment('服务'));
        $stub = $this->replaceParent($stub, HttpConstants::SERVICE_SUFFIX);
        $stub = str_replace(['{{ createHandle }}', '{{createHandle}}'], $this->getCreateStub(), $stub);
        $stub = $this->replaceBizField($stub);
        $stub = str_replace(['{{ bizIdWhere }}', '{{bizIdWhere}}'], $this->getBizIdWhereStub(), $stub);
        $stub = str_replace(['{{ fullModelClass }}', '{{fullModelClass}}'], $modelClass, $stub);
        $stub = str_replace(['{{ modelNamespace }}', '{{modelNamespace}}'], $modelNamespace, $stub);
        $stub = str_replace(['{{ handleModel }}', '{{handleModel}}'], $handleModel, $stub);
        $stub = str_replace(['{{ tableNameCamel }}', '{{tableNameCamel}}'], $this->getTableShortNameCamel(), $stub);
        $stub = str_replace(['{{ indexSearch }}', '{{indexSearch}}'], $this->getIndexSearchStub(), $stub);
        $stub = str_replace(['{{ primaryKey }}', '{{primaryKey}}'], $this->getPrimaryKey(), $stub);
        $stub = str_replace(['{{ deleteMethod }}', '{{deleteMethod}}'], $this->getDeleteMethod(), $stub);

        return $this->makeFile($classNamespace, HttpConstants::SERVICE_SUFFIX, $this->sortImports($stub));
    }

    /**
     * 获取搜索模板
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 11:34 AM
     */
    protected function getIndexSearchStub(): string
    {
        $keywordKey  = (array) explode(',', (string) $this->getOptionWithTrim('keyword_key'));
        $keywordKeys = array_values(array_intersect(array_keys($this->currentColumnList), $keywordKey));
        if (empty($keywordKeys)) {
            return '';
        }
        $keyCount     = count($keywordKeys);
        $keywordInput = $this->getOptionWithTrim('keyword');
        $likeStr      = sprintf("'%%'.%s.'%%'", '$keyword');
        $searchStr    = <<<EOD

        \$keyword = \$params['$keywordInput'] ?? null;\n
EOD;
        foreach ($keywordKeys as $key => $value) {
            // 只有一个关键字
            if ($keyCount === 1) {
                $searchStr .= <<<EOD
        if (! \$keyword) {
            return;
        }
        \$builder->where('$value', 'LIKE', $likeStr);
EOD;
                break;
            }

            $keyEnd = ($key + 1) === $keyCount;
            if ($key === 0) {
                $searchStr .= <<<EOD
        if (! \$keyword) {
            return;
        }
        \$keywordStr = $likeStr;
        \$builder->where(function (\$query) use (\$keywordStr) {
            /** @var Builder \$query */
            \$query->where('$value', 'LIKE', \$keywordStr)\n
EOD;
            } elseif ($keyEnd) {
                $searchStr .= <<<EOD
                  ->orWhere('$value', 'LIKE', \$keywordStr);
        });
EOD;
            } else {
                $searchStr .= <<<EOD
                  ->orWhere('$value', 'LIKE', \$keywordStr)\n
EOD;
            }
        }

        return $searchStr;
    }

    /**
     * 获取业务ID创建时，自动赋值模板
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 1:16 PM
     */
    protected function getCreateStub(): string
    {
        if ($this->isPkKeyWithBizId()) {
            // id自增，不创建
            return <<<EOD

        \$handleModel = app({{ handleModel }}::class);
        \$save        = Arr::only(\$params, \$handleModel->getFillable());
EOD;
        }

        return <<<EOD

        \$handleModel    = app({{ handleModel }}::class);
        \$save           = Arr::only(\$params, \$handleModel->getFillable());
        \$save['$this->bizColumnName'] = snowflakeId();
EOD;
    }

    /**
     * 获取删除使用的方法
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/16 7:50 PM
     */
    protected function getDeleteMethod(): string
    {
        if ($this->isPkKeyWithBizId()) {
            return 'deleteWithIds';
        }

        return 'deleteWithBizIds';
    }
}
