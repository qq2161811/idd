<?php

declare(strict_types = 1);

namespace IDD\Make\Commands\Make\Curd\Traits;


use IDD\Make\Parameter;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\PhpDateTimeMappingType;
use Illuminate\Support\Str;


/**
 * YAPI 文档生成
 * Trait YapiDocs
 *
 * @package IDD\Make\Commands\Make\Curd\Traits
 * @author  ShuQingZai<overbeck.jack@qq.com> 2022/3/19 10:39
 */
trait YapiDocs
{
    private string $classType = 'Yapi';

    /**
     * 生成 Yapi 文档文件
     *
     * @throws \JsonException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:59
     */
    protected function makeYapiDoc(): void
    {
        // 生成更新或创建 - 请求
        $this->genCreateOrUpdateWithReq();

        // 生成详情 - 响应
        $this->genDetailWithRely();

        // 生成列表 - 响应
        $this->genListWithRely();

        // 生成删除 - 请求
        $this->genDeleteWithReq();
    }

    /**
     * 生成 创建|更新 请求文档文件
     *
     * @return bool
     * @throws \JsonException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:55
     */
    protected function genCreateOrUpdateWithReq(): bool
    {
        $cuProperties = $this->getYapiStubWithCreateOrUpdate();
        $data         = $this->getYapiBaseStub('object', $cuProperties);
        $filePath     = $this->getYapiFileSavePath('createOrUpdateWithRequest');

        return $this->createFile($filePath, $this->classType, $this->formatYapiDocsToJson($data));
    }

    /**
     * 生成 详情 响应文档文件
     *
     * @return bool
     * @throws \JsonException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:55
     */
    protected function genDetailWithRely(): bool
    {
        $cuProperties = $this->getYapiStubWithDetail();
        $data         = $this->getYapiResponseStub('object', $cuProperties);
        $filePath     = $this->getYapiFileSavePath('detailWithRely');

        return $this->createFile($filePath, $this->classType, $this->formatYapiDocsToJson($data));
    }

    /**
     * 生成 列表 响应文件
     *
     * @return bool
     * @throws \JsonException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 17:39
     */
    protected function genListWithRely(): bool
    {
        $resProperties = $this->getYapiStubWithList();
        $itemSchema    = $this->getYapiBasePropertiesStub('object', $resProperties);
        $listSchema    = [
            'type'        => 'array',
            'items'       => $itemSchema,
            'uniqueItems' => true,
            'minItems'    => 1,
            'maxItems'    => Parameter::PAGE_MAX_SIZE,
            'description' => '记录列表',
        ];
        $countSchema   = [
            'type'        => 'integer',
            'mock'        => [
                'mock' => '@integer(0,9999999)',
            ],
            'default'     => 0,
            'description' => '记录总数',
        ];
        $properties    = [
            'list'  => $listSchema,
            'count' => $countSchema,
        ];

        $data     = $this->getYapiResponseStub('object', $properties);
        $filePath = $this->getYapiFileSavePath('listWithRely');

        return $this->createFile($filePath, $this->classType, $this->formatYapiDocsToJson($data));
    }

    /**
     * 生成 删除 响应文件
     *
     * @return bool
     * @throws \JsonException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 17:45
     */
    protected function genDeleteWithReq(): bool
    {
        $data     = array_merge(
            $this->getYapiStubWithDelete(),
            $this->getSchemaVersion()
        );
        $filePath = $this->getYapiFileSavePath('deleteWithRequest');

        return $this->createFile($filePath, $this->classType, $this->formatYapiDocsToJson($data));
    }

    /**
     * 格式化文档输出文件
     *
     * @param  array  $data
     * @return false|string
     * @throws \JsonException
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:55
     */
    protected function formatYapiDocsToJson(array $data)
    {
        return json_encode($data,
            JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }

    /**
     * 获取文档保存路径
     *
     * @param  string  $name  文件名称
     * @return string
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:50
     */
    protected function getYapiFileSavePath(string $name): string
    {
        $name = sprintf('%s_%s_%s', $this->getTableShortNameCamel(), Str::camel($name), date('H'));

        return storage_path(sprintf('yapi/%s/%s.json', date('Y-m-d'), $name));
    }

    /**
     * 获取响应
     *
     * @param  string  $type
     * @param  array   $resProperties
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:25
     */
    protected function getYapiResponseStub(string $type, array $resProperties): array
    {
        if ($type === 'array') {
            $itemSchema                = $this->getYapiBasePropertiesStub('object', $resProperties);
            $itemSchema['description'] = '记录列表';
        } else {
            $itemSchema = $this->getYapiBasePropertiesStub($type, $resProperties);
        }

        if ($type === 'array') {
            $dataSchema = [
                'type'        => $type,
                'items'       => $itemSchema,
                'uniqueItems' => true,
                'minItems'    => 1,
                'maxItems'    => 50,
            ];
        } elseif ($type === 'object') {
            $dataSchema = $itemSchema;
        } else {
            $dataSchema['type'] = $type;
        }

        $properties = [
            'code' => [
                'type'        => 'integer',
                'mock'        => [
                    'mock' => '@integer(0,1000)',
                ],
                'default'     => 0,
                'description' => '业务CODE',
            ],
            'msg'  => [
                'type'        => 'string',
                'mock'        => [
                    'mock' => '@string',
                ],
                'default'     => '请求成功',
                'description' => '业务提示语',
            ],
            'data' => array_merge(
                [
                    'description' => '响应内容',
                ],
                $dataSchema,
            ),
        ];

        return $this->getYapiBaseStub('object', $properties);
    }

    /**
     * 获取 yapi 基础模板
     *
     * @param  string  $type
     * @param  array   $properties
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:42
     */
    protected function getYapiBaseStub(string $type, array $properties): array
    {
        return $this->getYapiBasePropertiesStub($type, $properties, $this->getSchemaVersion());
    }

    /**
     * 获取 json Schema 版本
     *
     * @return string[]
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 17:57
     */
    protected function getSchemaVersion(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
        ];
    }

    /**
     * 获取基础文档模板
     *
     * @param  string  $type        字段类型 array|object
     * @param  array   $properties  子属性
     * @param  array   $append      自定义追加属性
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 16:30
     */
    protected function getYapiBasePropertiesStub(string $type, array $properties, array $append = []): array
    {
        $data = [
            'type' => $type,
        ];
        if ($properties) {
            $data['properties'] = $properties;
            $data['required']   = array_keys($properties);
        }

        return array_merge($data, $append);
    }

    /**
     * 获取 创建|更新 属性
     *
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 15:25
     */
    protected function getYapiStubWithCreateOrUpdate(): array
    {
        $properties = [];
        [$fillable,] = $this->getModelFillable('', [$this->bizColumnName]);
        foreach ($this->currentColumnList as $name => $item) {
            if (! in_array($name, $fillable, true)) {
                continue;
            }

            $properties[$item['nameCamel']] = $item['yapiDoc'];
        }

        return $properties;
    }

    /**
     * 获取详情属性
     *
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 15:25
     */
    protected function getYapiStubWithDetail(): array
    {
        $properties    = [];
        $deleteAtField = $this->curdModel->getDeletedAtColumn();
        foreach ($this->currentColumnList as $name => $item) {
            // 忽略 deleteAt 字段
            if ($name === $deleteAtField) {
                continue;
            }
            $properties[$item['nameCamel']] = $item['yapiDoc'];
        }

        return $properties;
    }

    /**
     * 获取列表属性
     *
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 15:25
     */
    protected function getYapiStubWithList(): array
    {
        $properties    = [];
        $deleteAtField = $this->curdModel->getDeletedAtColumn();
        foreach ($this->currentColumnList as $name => $item) {
            // 忽略 deleteAt 字段
            if ($name === $deleteAtField) {
                continue;
            }
            $properties[$item['nameCamel']] = $item['yapiDoc'];
        }

        return $properties;
    }

    /**
     * 获取批量删除
     *
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 15:27
     */
    protected function getYapiStubWithDelete(): array
    {
        return $this->getYapiBasePropertiesStub('object',
            [
                $this->getBizIdFieldNameCamel().'s' => [
                    'type'        => 'array',
                    'items'       => $this->getYapiDocWithBizId(),
                    'uniqueItems' => true,
                    'minItems'    => 1,
                    'maxItems'    => 50,
                    'description' => sprintf('%s集合', $this->getBizIdFieldNameCamel()),
                ],
            ]);
    }

    /**
     * 生成字段文档
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/19 14:18
     */
    protected function createJsonSchema(Column $item): array
    {
        $type    = $item->getType();
        $default = $item->getDefault();
        $comment = $this->getColumnComment($item);
        $data    = [];
        switch (true) {
            case $this->isEnum($item): // 枚举类型
                $enumValue        = $this->getEnumValue($item);
                $data['type']     = 'integer';
                $data['default']  = is_null($default) ? $enumValue[0] : $default;
                $data['enumDesc'] = $comment;
                $data['enum']     = $enumValue;
                $data['mock']     = [
                    'mock' => '@integer',
                ];

                break;
            case $this->isIntType($type): // 整型
                $data['type'] = 'integer';
                $mock         = '@integer';
                if ($this->isIdField($item->getName())) {
                    // ID字段处理
                    $data['default'] = 1;
                    $data['minimum'] = 1;
                    $mock            = '@integer(1,2147483647)';
                } elseif ($item->getUnsigned()) {
                    // 非负数
                    $data['minimum'] = 0;
                    $mock            = '@integer(0,2147483647)';
                }
                $data['mock']    = [
                    'mock' => $mock,
                ];
                $data['maximum'] = 2147483647;

                break;
            case $this->isPrice($item): // 价格字段
                $data['type']      = 'string';
                $data['mock']      = [
                    'mock' => '@string',
                ];
                $data['default']   = '0.00';
                $data['minLength'] = 1;
                $data['maxLength'] = 10;
                if ($item->getUnsigned()) {
                    // 非负数
                    $pattern = '(^([1-9]\\d*){1,8}\\.(\\d{2})$)|(^0\\.(\\d{2})$)';
                } else {
                    $pattern = '(^((-)?[1-9]\\d*){1,7}\\.(\\d{2})$)|(^(-)?0\\.(\\d{2})$)';
                }
                $data['pattern'] = $pattern;

                break;
            case $type instanceof FloatType:
                $data['type'] = 'number';
                $maxNum       = $this->getNumberWithFloat($item, true);
                if ($item->getUnsigned()) {
                    // 非负数
                    $data['minimum'] = 0.0;
                }
                $mock            = sprintf('@float(0, %s, %d, %d)', (string) $maxNum, $item->getScale(),
                    $item->getScale());
                $data['mock']    = [
                    'mock' => $mock,
                ];
                $data['maximum'] = $maxNum;

                break;
            case $type instanceof DateType:
            case $type instanceof PhpDateTimeMappingType:
                $data['type']    = 'string';
                $data['mock']    = [
                    'mock' => '@datetime',
                ];
                $data['format']  = 'date-time';
                $data['default'] = '';

                break;
            case $type instanceof JsonType:
                $data['type']        = 'array';
                $data['items']       = [
                    'type' => 'string',
                ];
                $data['uniqueItems'] = true;
                $data['minItems']    = 1;
                $data['maxItems']    = 50;

                break;
            default:
                $data['type']      = 'string';
                $data['minLength'] = 1;
                $data['maxLength'] = $this->getStringLength($item);
                $data              = array_merge($data, $this->stringDoc($item));

                break;
        }
        $data['description'] = $comment;
        if (! isset($data['default']) && ! is_null($default)) {
            $data['default'] = $default;
        }
        if (($data['default'] ?? null) === '') {
            unset($data['default']);
        }

        return $data;
    }


    /**
     * 处理 字符串 验证规则
     *
     * @param  \Doctrine\DBAL\Schema\Column  $item
     * @return array
     * @author ShuQingZai<overbeck.jack@qq.com> 2022/3/2 3:11 PM
     */
    protected function stringDoc(Column $item): array
    {
        $name = $item->getName();
        if ($this->isURLField($name) || $this->isURLField($name, 'image') || $this->isURLField($name, 'file')) {
            // URL地址
            return [
                'mock'    => [
                    'mock' => '@url',
                ],
                'format'  => 'uri',
                'pattern' => '^http(s?):\/\/((\w+\/)+)?\w\.[a-z]{2,5}$',
            ];
        }
        if ($this->isBizIdField($name)) {
            // bizId
            return $this->getYapiDocWithBizIdString($item);
        }

        // 其他普通

        return [
            'mock' => [
                'mock' => '@string',
            ],
        ];
    }
}
