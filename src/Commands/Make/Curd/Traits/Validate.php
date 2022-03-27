<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


use IDD\Make\HttpConstants;
use IDD\Make\Rules\InArray;
use IDD\Make\Rules\IsBizIdValue;
use IDD\Make\Rules\IsIdValue;
use IDD\Make\Rules\IsNegativePrice;
use IDD\Make\Rules\IsPrice;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\PhpDateTimeMappingType;


/**
 * 验证器相关
 * Trait Validate
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/2/21 10:06 AM
 */
trait Validate
{
    /**
     * 创建 validate 文件
     *
     * @return false
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 4:37 PM
     */
    protected function makeValidate(string $name): bool
    {
        [$className, $classNamespace] = $this->parseClass(
            $name,
            HttpConstants::PATH_NAME_VALIDATE,
            HttpConstants::VALIDATE_SUFFIX
        );
        [$rulesStub, $messageStub] = $this->getValidateRulesStub();

        $stub = $this->getStub(HttpConstants::VALIDATE_SUFFIX);
        $stub = $this->replaceNamespace($stub, $classNamespace)->replaceClass($stub, $className);
        $stub = $this->replaceDescription($stub, $this->getClassComment('验证器'));
        $stub = $this->replaceParent($stub, HttpConstants::VALIDATE_SUFFIX);
        $stub = $this->replaceBizField($stub);
        $stub = str_replace(['{{ createScene }}', '{{createScene}}'], $this->getCreateSceneStub(), $stub);
        $stub = str_replace(['{{ updateScene }}', '{{updateScene}}'], $this->getUpdateSceneStub(), $stub);
        $stub = str_replace(['{{ rules }}', '{{rules}}'], $rulesStub, $stub);
        $stub = str_replace(['{{ messages }}', '{{messages}}'], $messageStub, $stub);

        return $this->makeFile($classNamespace, HttpConstants::VALIDATE_SUFFIX, $this->sortImports($stub));
    }

    /**
     * 获取验证器 create 场景验证模板
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 4:57 PM
     */
    protected function getCreateSceneStub(): string
    {
        $glueSad = $this->getStrIndent(12);
        [, $fillableStr] = $this->getModelFillable($glueSad, [$this->bizColumnName]);

        return $fillableStr;
    }

    /**
     * 获取验证器 update 场景验证模板
     *
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 4:57 PM
     */
    protected function getUpdateSceneStub(): string
    {
        $glueSad = $this->getStrIndent(12);
        [, $fillableStr] = $this->getModelFillable($glueSad, [$this->bizColumnName]);

        return $fillableStr;
    }

    /**
     * 获取验证器验证规则与提示语
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 7:05 PM
     */
    protected function getValidateRulesStub(): array
    {
        [$fillable,] = $this->getModelFillable($this->getStrIndent(16), [$this->bizColumnName]);
        if (empty($fillable)) {
            return ['', ''];
        }
        $message = [];
        $rules   = [];
        foreach ($fillable as $key) {
            $item = $this->currentColumnList[$key] ?? null;
            if (! $item) {
                continue;
            }

            $itemRules  = $item['validates']['rules'] ?? [];
            $ruleStr    = '[';
            $countRules = count($itemRules);
            foreach ($itemRules as $k => $rule) {
                $skip = $k + 1 === $countRules ? '' : ' ';
                if ($this->isNewObject($rule)) {
                    $ruleStr .= sprintf("%s,%s", $rule, $skip);
                } else {
                    $ruleStr .= sprintf("'%s',%s", $rule, $skip);
                }
            }
            $ruleStr     = rtrim($ruleStr, ',');
            $ruleStr     .= ']';
            $rules[$key] = $ruleStr;
            $message     = array_merge($message, $item['validates']['message'] ?? []);
        }
        $rulesStr    = $this->arrayToString($rules, 16);
        $messageStr  = $this->arrayToString($message, 16);
        $rulesStub   = <<<EOD

$rulesStr
EOD;
        $messageStub = <<<EOD

$messageStr
EOD;

        return [$rulesStub, $messageStub];
    }

    /**
     * 生成验证器验证信息与提示语
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 2:13 PM
     */
    protected function createValidateRule(Column $item): array
    {
        $type        = $item->getType();
        $rules       = ['required'];
        $isString    = false;
        $name        = $item->getName();
        $commentName = $this->getCommentName($item);
        switch (true) {
            case $this->isEnum($item): // 枚举类型
                $msg          = sprintf('请选择%s', $commentName);
                $enumValue    = $this->getEnumValue($item);
                $enumValueStr = $this->arrayToStrWithNumber($enumValue, '');
                $rules[]      = sprintf(
                    "new \\%s([%s], '%s')",
                    InArray::class,
                    rtrim($enumValueStr, ','),
                    $msg);

                break;
            case $this->isPrice($item): // 价格字段
                $msg = sprintf('%s错误', $commentName);
                if ($item->getUnsigned()) {
                    $vClass = IsPrice::class;
                } else {
                    $vClass = IsNegativePrice::class;
                }
                $rules[] = 'string';
                $rules[] = sprintf("new \\%s('%s')", $vClass, $msg);

                break;
            case $this->isIntType($type):
                if ($this->isIdField($name)) {
                    // id字段
                    [$idRules, $idMsg] = $this->getIdRules($item);
                    $rules = [...$rules, ...$idRules];
                    $msg   = $idMsg;
                } else {
                    $rules[] = 'integer';
                    if ($item->getUnsigned()) {
                        $rules[] = 'gte:0';
                        $msg     = sprintf('请填写%s，只能是整数且不能是负数', $commentName);
                    } else {
                        $msg = sprintf('请填写%s，必须是整数', $commentName);
                    }
                }
                break;
            case $type instanceof DateType:
                $rules[] = 'date';
                $msg     = sprintf('请选择%s，必须是一个有效的日期', $commentName);
                break;
            case $type instanceof PhpDateTimeMappingType:
                $rules[] = 'datetime';
                $msg     = sprintf('请选择%s，必须是一个有效的日期时间', $commentName);
                break;
            case $type instanceof JsonType:
                $rules[] = 'array';
                $msg     = sprintf('%s错误', $commentName);
                break;
            case $type instanceof DecimalType:
                $rules[] = 'string';
                $rules[] = 'numeric';
                if ($item->getUnsigned()) {
                    $rules[] = 'gt:0';
                    $msg     = sprintf('请填写%s，必须是正数', $commentName);
                } else {
                    $msg = sprintf('请填写%s，必须是数字', $commentName);
                }
                break;
            case  $type instanceof FloatType:
                $rules[] = 'numeric';
                if ($item->getUnsigned()) {
                    $rules[] = 'gte:0';
                    $msg     = sprintf('请填写%s，只能是数字且不能是负数', $commentName);
                } else {
                    $msg = sprintf('请填写%s，必须是数字', $commentName);
                }
                break;
            default:
                $msg      = '';
                $isString = true;
                break;
        }
        if ($isString) {
            [$myRules, $msg] = $this->handleString($item);
            $rules = [...$rules, ...$myRules];
        }

        $rules   = array_values(array_unique($rules));
        $ruleMsg = [];
        foreach ($rules as $rule) {
            // 验证规则为 new 对象时，跳过验证提示语，应该在 对象中返回
            if ($this->isNewObject($rule)) {
                continue;
            }
            $ruleName = explode(':', $rule)[0] ?? $rule;

            $ruleMsg[sprintf('%s.%s', $name, $ruleName)] = $msg;
        }

        return [
            'rules'   => $rules,
            'message' => $ruleMsg,
        ];
    }

    /**
     * 处理 字符串 验证规则
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 3:11 PM
     */
    protected function handleString(Column $item): array
    {
        $name        = $item->getName();
        $len         = $this->getStringLength($item);
        $rules       = ['string', 'max:'.$len];
        $commentName = $this->getCommentName($item);
        if ($this->isURLField($name)) {
            // url
            $msg     = sprintf('请填写%s，必须是一个有效的URL地址', $commentName);
            $rules[] = 'url';

            return [$rules, $msg];
        }
        if ($this->isURLField($name, 'image') || $this->isURLField($name, 'file')) {
            // 图片
            $msg     = sprintf('请上传%s', $commentName);
            $rules[] = 'url';

            return [$rules, $msg];
        }
        if ($this->isBizIdField($name)) {
            // bizId
            return $this->getBizIdRules($item);
        }
        // 其他普通
        $cLen = (int) ($len / 3);
        $msg  = sprintf('请填写%s，%d个字符或%d个中文字内有效', $commentName, $len, $cLen);

        return [$rules, $msg];
    }

    /**
     * 获取 ID 字段验证
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:13 PM
     */
    protected function getIdRules(Column $item): array
    {
        $rules        = [];
        $comment      = strtolower($this->getColumnComment($item));
        $searchList   = $this->getModalListWithPrefix('id');
        $searchList[] = 'id';
        $formatMsg    = sprintf("%s错误", str_replace($searchList, '', $comment));
        $rules[]      = sprintf("new \\%s('%s')", IsIdValue::class, $formatMsg);

        return [$rules, $formatMsg];
    }

    /**
     * 获取 BizId 字段验证
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:13 PM
     */
    protected function getBizIdRules(Column $item): array
    {
        $rules      = [];
        $comment    = strtolower($this->getColumnComment($item));
        $searchList = [
            ...$this->getModalListWithPrefix('biz_id'),
            ...$this->getModalListWithPrefix('bizId'),
            ...$this->getModalListWithPrefix('业务id'),
            ...['biz_id', 'bizId', '业务id'],
        ];
        $formatMsg  = sprintf("%s错误", str_replace($searchList, '', $comment));
        $rules[]    = sprintf("new \\%s('%s')", IsBizIdValue::class, $formatMsg);

        return [$rules, $formatMsg];
    }

    /**
     * 常用语气词
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:05 PM
     */
    protected function getModalList(): array
    {
        return [
            '的', '了', '么', '呢', '吧', '啊', '得', '地',
        ];
    }

    /**
     * 获取带有语气词前缀的注释列表 -- 全部英文字母转换为小写
     *
     * @param  string  $str  字段注释
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 2:07 PM
     */
    protected function getModalListWithPrefix(string $str): array
    {
        $list = [];
        $str  = strtolower($str);
        foreach ($this->getModalList() as $item) {
            $list[] = $item.$str;
        }

        return $list;
    }
}
