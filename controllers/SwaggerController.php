<?php

class SwaggerController extends MiniEngine_Controller
{
    public function indexAction()
    {
        header('Content-Type: text/yaml');
        echo $this->generate();
        return $this->noview();
    }

    public function uiAction()
    {
        //
    }

    protected function pascal2Underscore(string $pascal): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $pascal));
    }

    protected function underscore2Pascal(string $underscore): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $underscore)));
    }

    protected function getEndPointPath(string $entity, string $class_name, string $endpoint_type, ?string $relation_name = null): string
    {
        $resource = $this->pascal2Underscore($entity) . 's';
        $id_fields = array_column($class_name::getIdFieldsInfo(), 'path_name');
        $id_fields_string = implode('/', array_map(fn($field) => '{' . $field . '}', $id_fields));
        switch ($endpoint_type) {
        case 'list':
            return "/{$resource}";
        case 'item':
            return "/{$resource}/{$id_fields_string}";
        case 'relation':
            return "/{$resource}/{$id_fields_string}/{$relation_name}";
        }
    }

    protected function getOperationId(string $entity, string $endpoint_type, ?string $relation_name = null): string
    {
        switch ($endpoint_type) {
        case 'list':
            return "list{$entity}s";
        case 'item':
            return "get{$entity}";
        case 'relation':
            return "get{$entity}" . $this->underscore2Pascal($relation_name);
        }
    }

    protected function getEndpointSummary(string $type_subject, string $endpoint_type): string
    {
        switch ($endpoint_type) {
        case 'list':
            return "取得{$type_subject}列表";
        case 'item':
            return "取得特定{$type_subject}資訊";
        }
    }

    protected function getFilterParameters(string $class_name): array
    {
        $parameters = [];
        foreach ($class_name::getFilterFieldsInfo() as $field => $info) {
            $parameters[] = [
                'name' => $field,
                'in' => 'query',
                'description' => $info['description'],
                'required' => false,
                'schema' => [
                    'type' => $info['type'],
                    'enum' => $info['enum'] ?? null,
                ],
            ];
        }
        $parameters[] = [
            'name' => 'page',
            'in' => 'query',
            'description' => '頁數',
            'required' => false,
            'schema' => [
                'type' => 'integer',
            ],
            'example' => 1,
        ];
        $parameters[] = [
            'name' => 'per_page',
            'in' => 'query',
            'description' => '每頁筆數',
            'required' => false,
            'schema' => [
                'type' => 'integer',
            ],
            'example' => 20,
        ];
        return $parameters;
    }

    protected function getIdParameters(string $class_name): array
    {
        $parameters = [];
        foreach ($class_name::getIdFieldsInfo() as $name => $info) {
            $parameters[] = [
                'name' => $info['path_name'],
                'description' => $name,
                'in' => 'path',
                'required' => true,
                'schema' => [
                    'type' => $info['type'],
                ],
                'example' => $info['example'],
            ];
        }
        return $parameters;
    }

    protected function getParameters(string $class_name, string $endpoint_type, ?string $relation_type = null): array
    {
        switch ($endpoint_type) {
        case 'list':
            return $this->getFilterParameters($class_name);
        case 'item':
            return $this->getIdParameters($class_name);
        case 'relation':
            $relation_entity = $this->underscore2Pascal($relation_type);
            $relation_class_name = $this->getClassNameByEntity($relation_entity);
            if (class_exists($relation_class_name)) {
                return array_merge(
                    $this->getIdParameters($class_name),
                    $this->getFilterParameters($relation_class_name),
                );
            } else {
                return $this->getIdParameters($class_name);
            }
        }
    }

    protected function getResponses(string $subject, ?string $schema_ref = null): stdClass
    {
        $response_200 = [
            'description' => sprintf('%s資料', $subject),
        ];

        if ($schema_ref) {
            $response_200['content'] = [
                'application/json' => [
                    'schema' => [
                        '$ref' => $schema_ref,
                    ],
                ],
            ];
        }

        // 要轉成 stdClass 否則 key 就算加了引號還是會被轉成數字
        return (object)[
            '200' => $response_200,
            '404' => [
                'description' => sprintf('找不到%s資料', $subject),
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => '#/components/schemas/Error'
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getClassNameByEntity(string $entity): string
    {
        return 'LYAPI_Type_' . $entity;
    }

    protected function generatePathsFromFile($file): array
    {
        $paths = [];
        $entity = basename($file, '.php');
        $class_name = $this->getClassNameByEntity($entity);
        $endpoint_types = $class_name::getEndpointTypes();
        if (empty($endpoint_types)) {
            return [];
        }
        //echo "[Generate from {$file}]\n";

        $group = method_exists($class_name, 'getEnpointGroup') ? $class_name::getEnpointGroup() : $entity;

        // 該 type 設定的 endpoint types
        foreach ($endpoint_types as $endpoint_type) {
            $base_path = $this->getEndPointPath($entity, $class_name, $endpoint_type);
            $paths[$base_path] = [
                'get' => [
                    'tags' => [$group],
                    'summary' => $this->getEndpointSummary($class_name::getTypeSubject(), $endpoint_type),
                    'operationId' => $this->getOperationId($entity, $endpoint_type),
                    'parameters' => $this->getParameters($class_name, $endpoint_type),
                    'responses' => $this->getResponses($class_name::getTypeSubject(), $this->getSchemaRef($entity, $endpoint_type)),
                ],
            ];
        }

        // 該 type 的 relation
        foreach ($class_name::getRelations() as $relation_name => $info) {
            $base_path = $this->getEndPointPath($entity, $class_name, 'relation', $relation_name);
            $paths[$base_path] = [
                'get' => [
                    'tags' => [$group],
                    'summary' => $this->getEndpointSummary($info['subject'], 'list'),
                    'operationId' => $this->getOperationId($entity, 'relation', $relation_name),
                    'parameters' => $this->getParameters($class_name, 'relation', $info['type']),
                    'responses' => $this->getResponses($info['subject'] ?? '', $this->getSchemaRef($info['type'], 'relation')),
                ],
            ];
        }

        return $paths;
    }

    protected function getSchemaRef(string $entity, string $endpoint_type): ?string
    {
        $class_name = $this->getClassNameByEntity($entity);
        switch ($endpoint_type) {
        case 'item':
            if (!empty($class_name::getItemProperties())) {
                return "#/components/schemas/{$entity}";
            }
            break;
        case 'list':
            if (!empty($class_name::getEntryProperties())) {
                return "#/components/schemas/{$entity}List";
            }
            break;
        case 'relation':
            // TODO
            break;
        }
        return null;
    }

    protected function generateSchemasFromFile($file): ?array
    {
        $entity = basename($file, '.php');
        $class_name = $this->getClassNameByEntity($entity);
        if (!class_exists($class_name)) {
            return null;
        }
        $schemas = [];
        //echo "[Generate schema from {$file}]\n";

        if (!empty($class_name::getItemProperties())) {
            $schemas[$entity] = [
                'type' => 'object',
                'properties' => $class_name::getItemProperties(),
            ];
        }

        if (!empty($class_name::getEntryProperties())) {
            // list schema
            $items_key = sprintf('%ss', strtolower($entity));
            $schemas["{$entity}List"] = [
                'type' => 'object',
                'properties' => [
                    'total' => [
                        'type' => 'integer',
                    ],
                    'total_pages' => [
                        'type' => 'integer',
                    ],
                    'page' => [
                        'type' => 'integer',
                    ],
                    'limit' => [
                        'type' => 'integer',
                    ],
                    'filter' => [
                        'type' => 'object',
                    ],
                    'id_fields' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                    'sort' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                    'output_fields' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                        ],
                    ],
                    $items_key => [
                        'type' => 'array',
                        'items' => [
                            '$ref' => "#/components/schemas/{$entity}Entry",
                        ],
                    ],
                ],
            ];

            // entry schema
            $schemas["{$entity}Entry"] = [
                'type' => 'object',
                'properties' => $class_name::getEntryProperties(),
            ];
        }

        return $schemas;
    }

    protected function parseToYaml($data, $indent = ''): string
    {
        $yaml = '';
        foreach ($data as $key => $value) {
            if (is_array($value) or $value instanceof stdClass) {
                if (is_int($key)) {
                    $yaml .= "{$indent}-\n";
                    $yaml .= $this->parseToYaml($value, $indent . '  ');
                } else {
                    $yaml .= "{$indent}{$key}:\n";
                    $yaml .= $this->parseToYaml($value, $indent . '  ');
                }
            } elseif (is_bool($value)) {
                $yaml .= "{$indent}{$key}: " . ($value ? 'true' : 'false') . "\n";
            } elseif (is_int($key)) {
                $yaml .= "{$indent}- {$value}\n";
            } elseif (is_string($value)) {
                $yaml .= "{$indent}{$key}: '{$value}'\n";
            } elseif (!empty($value)) {
                $yaml .= "{$indent}{$key}: {$value}\n";
            }
        }
        return $yaml;
    }

    protected function generate()
    {
        $auto_gen_files = [
            MINI_ENGINE_ROOT . '/libraries/LYAPI/Type/*.php',
        ];

        $data = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => '立法院 API v2',
                'version' => '2.0.0',
            ],
            'servers' => [
                ['url' => 'https://ly.govapi.tw/v2'],
            ],
        ];

        $data['paths'] = [
            '/stat' => [
                'get' => [
                    'tags' => ['Stat'],
                    'summary' => '取得統計資料',
                    'operationId' => 'getStat',
                    'responses' => (object)[  // 要轉成 stdClass 否則 key 就算加了引號還是會被轉成數字
                        '200' => [
                            'description' => '統計資料',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($auto_gen_files as $file) {
            foreach (glob($file) as $f) {
                $paths = $this->generatePathsFromFile($f);
                $data['paths'] = array_merge($data['paths'], $paths);
            }
        }

        $data['components'] = [
            'schemas' => [
                'Error' => [
                    'required' => ['error'],
                    'properties' => [
                        'error' => [
                            'type' => 'boolean',
                        ],
                        'message' => [
                            'type' => 'string',
                        ],
                    ],
                    'type' => 'object',
                ],
            ],
        ];

        foreach ($auto_gen_files as $file) {
            foreach (glob($file) as $f) {
                $schemas = $this->generateSchemasFromFile($f) ?? [];
                foreach ($schemas as $name => $schema) {
                    $data['components']['schemas'][$name] = $schema;
                }
            }
        }

        return $this->parseToYaml($data);
    }
}

