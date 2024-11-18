<?php

include(__DIR__ . '/../init.inc.php');

function pascal2Underscore(string $pascal): string {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $pascal));
}

function underscore2Pascal(string $underscore): string {
    return str_replace(' ', '', ucwords(str_replace('_', ' ', $underscore)));
}

function getEndPointPath(string $entity, string $class_name, string $endpoint_type, ?string $relation_name = null): string {
    $resource = pascal2Underscore($entity) . 's';
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

function getOperationId(string $entity, string $endpoint_type, ?string $relation_name = null): string {
    switch ($endpoint_type) {
        case 'list':
            return "list{$entity}s";
        case 'item':
            return "get{$entity}";
        case 'relation':
            return "get{$entity}" . underscore2Pascal($relation_name);
    }
}

function getEndpointSummary(string $type_subject, string $endpoint_type): string {
    switch ($endpoint_type) {
        case 'list':
            return "取得{$type_subject}列表";
        case 'item':
            return "取得特定{$type_subject}資訊";
    }
}

function getFilterParameters(string $class_name) {
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

function getIdParameters(string $class_name) {
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

function getParameters(string $class_name, string $endpoint_type, ?string $relation_type = null) {
    switch ($endpoint_type) {
        case 'list':
            return getFilterParameters($class_name);
        case 'item':
            return getIdParameters($class_name);
        case 'relation':
            $relation_entity = underscore2Pascal($relation_type);
            $relation_class_name = getClassNameByEntity($relation_entity);
            if (class_exists($relation_class_name)) {
                return array_merge(
                    getIdParameters($class_name),
                    getFilterParameters($relation_class_name),
                );
            } else {
                return getIdParameters($class_name);
            }
    }
}

function getResponses(string $subject) {
    // 要轉成 stdClass 否則 key 就算加了引號還是會被轉成數字
    return (object)[
        '200' => [
            'description' => sprintf('%s資料', $subject),
        ],
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

function getClassNameByEntity(string $entity) {
    return 'LYAPI_Type_' . $entity;
}

function generatePathsFromFile($file) {
    $paths = [];
    $entity = basename($file, '.php');
    $class_name = getClassNameByEntity($entity);
    $ref = "#/components/schemas/{$entity}";
    $endpoint_types = $class_name::getEndpointTypes();
    if (empty($endpoint_types)) {
        return [];
    }
    echo "[Generate from {$file}]\n";

    $group = method_exists($class_name, 'getEnpointGroup') ? $class_name::getEnpointGroup() : $entity;

    // 該 type 設定的 endpoint types
    foreach ($endpoint_types as $endpoint_type) {
        $base_path = getEndPointPath($entity, $class_name, $endpoint_type);
        $paths[$base_path] = [
            'get' => [
                'tags' => [$group],
                'summary' => getEndpointSummary($class_name::getTypeSubject(), $endpoint_type),
                'operationId' => getOperationId($entity, $endpoint_type),
                'parameters' => getParameters($class_name, $endpoint_type),
                'responses' => getResponses($class_name::getTypeSubject()),
            ],
        ];
    }

    // 該 type 的 relation
    foreach ($class_name::getRelations() as $relation_name => $info) {
        $base_path = getEndPointPath($entity, $class_name, 'relation', $relation_name);
        $paths[$base_path] = [
            'get' => [
                'tags' => [$group],
                'summary' => getEndpointSummary($info['subject'], 'list'),
                'operationId' => getOperationId($entity, 'relation', $relation_name),
                'parameters' => getParameters($class_name, 'relation', $info['type']),
                'responses' => getResponses($info['subject'] ?? ''),
            ],
        ];
    }

    return $paths;
}


$auto_gen_files = [
    MINI_ENGINE_ROOT . '/libraries/LYAPI/Type/*.php',
];

$data = [
    'openapi' => '3.0.0',
    'info' => [
        'title' => '立法院 API v2',
        'version' => '2.0.0',
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
        $paths = generatePathsFromFile($f);
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

function parseToYaml($data, $indent = '') {
    $yaml = '';
    foreach ($data as $key => $value) {
        if (is_array($value) or $value instanceof stdClass) {
            if (is_int($key)) {
                $yaml .= "{$indent}-\n";
                $yaml .= parseToYaml($value, $indent . '  ');
            } else {
                $yaml .= "{$indent}{$key}:\n";
                $yaml .= parseToYaml($value, $indent . '  ');
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

$result = parseToYaml($data);
file_put_contents(MINI_ENGINE_ROOT . '/swagger.yaml', $result);
