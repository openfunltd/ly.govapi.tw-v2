<?php

include(__DIR__ . '/../init.inc.php');

function pascal2Underscore(string $pascal): string {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $pascal));
}

function getEndPointPath(string $entity, string $class_name, string $endpoint_type): string {
    $resource = pascal2Underscore($entity) . 's';
    switch ($endpoint_type) {
        case 'list':
            return "/{$resource}";
        case 'item':
            $id_fields = array_column($class_name::getIdFieldsInfo(), 'path_name');
            $id_fields_string = implode('-', array_map(fn($field) => '{' . $field . '}', $id_fields));
            return "/{$resource}/{$id_fields_string}";
    }
}

function getOperationId(string $entity, string $endpoint_type): string {
    switch ($endpoint_type) {
        case 'list':
            return "list{$entity}s";
        case 'item':
            return "get{$entity}";
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

function getParameters(string $class_name, string $endpoint_type) {
    $parameters = [];
    switch ($endpoint_type) {
        case 'list':
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
            break;
        case 'item':
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
            break;
    }
    return $parameters;
}

function getResponses(string $class_name, string $endpoint_type) {
    // 要轉成 stdClass 否則 key 就算加了引號還是會被轉成數字
    return (object)[
        '200' => [
            'description' => sprintf('%s資料', $class_name::getTypeSubject()),
        ],
        '404' => [
            'description' => sprintf('找不到%s資料', $class_name::getTypeSubject()),
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

function generatePathsFromFile($file) {
    $paths = [];
    $entity = basename($file, '.php');
    $class_name = 'LYAPI_Type_' . $entity;
    $ref = "#/components/schemas/{$entity}";
    $endpoint_types = $class_name::getEndpointTypes();
    if (empty($endpoint_types)) {
        return [];
    }
    echo "[Generate from {$file}]\n";
    foreach ($endpoint_types as $endpoint_type) {
        $base_path = getEndPointPath($entity, $class_name, $endpoint_type);
        $group = method_exists($class_name, 'getEnpointGroup') ? $class_name::getEnpointGroup() : $entity;
        $paths[$base_path] = [
            'get' => [
                'tags' => [$group],
                'summary' => getEndpointSummary($class_name::getTypeSubject(), $endpoint_type),
                'operationId' => getOperationId($entity, $endpoint_type),
                'parameters' => getParameters($class_name, $endpoint_type),
                'responses' => getResponses($class_name, $endpoint_type),
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
