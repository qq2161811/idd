<?php

declare(strict_types = 1);

namespace IDD\Framework\Commands\Make\Curd\Traits;


/**
 * 数组与字符串模板处理
 * Trait ArrayAndString
 *
 * @package IDD\Framework\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/3/20 4:17 PM
 */
trait ArrayAndString
{

    /**
     * 将数据转换成带字符串 - 字符串索引
     *
     * @param  array  $arr
     * @param  int    $glueSadLen
     * @return string
     */
    protected function arrayToString(array $arr, int $glueSadLen = 8): string
    {
        $maxLen = 0;
        foreach ($arr as $key => $item) {
            $keyLen = strlen((string) $key);
            if ($keyLen > $maxLen) {
                $maxLen = $keyLen;
            }
        }
        $strArr  = [];
        $i       = 0;
        $glueSad = $this->getStrIndent($glueSadLen);
        foreach ($arr as $k => $v) {
            ++$i;
            $format   = $this->getStrFormatType($v);
            $vStr     = null;
            $vStrTemp = (string) $v;
            // 类函数处理
            $tempClassStr = [];
            if (str_contains($vStrTemp, '::')) {
                $tempClassStr = explode('::', $vStrTemp);
            } elseif (str_contains($vStrTemp, '->')) {
                $tempClassStr = explode('::', $vStrTemp);
            }
            if (class_exists($tempClassStr[0] ?? '')) {
                $vStr = $v;
            }

            if (is_null($vStr)) {
                if (class_exists($vStrTemp)) {
                    $vStr = $v.'::class';
                } elseif ($format === '%s') {
                    $isVar = in_array(substr($v, 0, 1), ['$', '_', '[']);
                    if (! $isVar) {
                        $v = str_replace("'", "\'", $v);
                        $k = str_replace("'", "\'", $k);
                    }
                    $vStr = ($isVar ? $v : "'$v'");
                } elseif ($format === '%f') {
                    $format = '%s';
                    $vStr   = $v;
                }
            }

            if (1 === $i) {
                $kStr = $glueSad."'".$k."'";
            } else {
                $kStr = "'".$k."'";
            }
            $str = sprintf(
                "%s%s => $format,",
                $kStr,
                $this->getStrIndent($maxLen - strlen($k)),
                $vStr);

            $strArr[] = $str;
        }

        return implode(PHP_EOL.$glueSad, $strArr);
    }

    /**
     * 倍增字符串
     *
     * @param  int     $padLen  长度
     * @param  string  $padStr  字符串
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/2/16 5:05 PM
     */
    protected function getStrIndent(int $padLen = 8, string $padStr = ' '): string
    {
        return str_pad('', $padLen, $padStr, STR_PAD_LEFT);
    }


    /**
     * 将数据转换成带字符串 - 数字索引
     *
     * @param  array   $list     数据
     * @param  string  $glueSad  缩进
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/20 4:24 PM
     */
    protected function arrayToStrWithNumber(array $list, string $glueSad): string
    {
        $maxLen    = 120;
        $resultStr = '';
        $columnStr = '';
        $count     = count($list);
        foreach ($list as $key => $item) {
            $skip         = ' ';
            $columnStrCut = false;

            if ($key === 0) {
                $resultStr = $glueSad;
                $columnStr = $glueSad;
            }

            if ($key + 1 === $count) {
                $skip = '';
            }

            $format = $this->getStrFormatType($item);
            if ($format === '%s') {
                $format = "'%s'";
            }
            $itemStr = sprintf("$format,%s", $item, $skip);
            if ((strlen($columnStr) + strlen($itemStr)) >= $maxLen) {
                $columnStrCut = true;
            }

            if ($columnStrCut) {
                $columnStr = $glueSad;
                $resultStr = rtrim($resultStr);
                $resultStr .= (PHP_EOL.$glueSad);
            } else {
                $columnStr .= $itemStr;
            }

            $resultStr .= $itemStr;
        }

        return $resultStr;
    }

    /**
     * 获取格式化类型
     *
     * @param $value
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/21 21:33
     */
    protected function getStrFormatType($value): string
    {
        if (is_int($value)) {
            return '%d';
        }
        if (is_float($value)) {
            return '%f';
        }

        return '%s';
    }
}
