<?php

class LYAPI_SearchAction
{
    protected static $_params = [];

    protected static function getParam($k, $default = null)
    {
        foreach (self::$_params as $param) {
            if ($param[0] == $k) {
                return $param[1];
            }
        }
        return $default;
    }

    protected static function getParams($k, $default = null)
    {
        $ret = [];
        foreach (self::$_params as $param) {
            if ($param[0] == $k) {
                $ret[] = $param[1];
            }
        }
        return $ret;
    }

    protected static function setQueryString($query_string)
    {
        $query_string = trim($query_string, '&');
        if (trim($query_string) == '') {
            self::$_params = [];
            return;
        }
        self::$_params = array_map(function($t) {
            if (strpos($t, '=') !== false) {
                return array_map('urldecode', explode('=', $t, 2));
            }
            if (strpos($t, ':') !== false) {
                list($k, $range) = explode(':', $t, 2);
                $range = explode(',', $range);
                return [urldecode($k), $range];
            }
            throw new Exception("不支援的參數格式：{$t}");
        }, explode('&', $query_string));
    }

    public static function getCollections($type, $query_string)
    {
        self::setQueryString($query_string);
        $cmd = new StdClass;
        $cmd->track_total_hits = true;
        $cmd->query = new StdClass;
        $cmd->query->bool = new StdClass;
        $cmd->query->bool->must = [];

        $records = new StdClass;
        $records->total = 0;
        $records->total_page = 0;
        $records->page = intval(self::getParam('page', 1));
        $records->limit = intval(self::getParam('limit', LYAPI_Type::run($type, 'defaultLimit')));
        $records->filter = new StdClass;
        $records->id_fields = LYAPI_Type::run($type, 'getIdFields');
        $cmd->size = $records->limit;
        $cmd->from = ($records->page - 1) * $records->limit;

        $default_output_fields = LYAPI_Type::run($type, 'outputFields');
        if (self::getParams('output_fields')) {
            $output_fields = self::getParams('output_fields');
        } elseif (count($default_output_fields)) {
            $output_fields = $default_output_fields;
        } else {
            $output_fields = null;
        }

        $filter_fields = LYAPI_Type::run($type, 'filterFields');
        $default_sort_fields = LYAPI_Type::run($type, 'sortFields');
        if (self::getParams('sort')) {
            $sort_fields = self::getParams('sort');
        } else {
            $sort_fields = $default_sort_fields;
        }
        if ($sort_fields) {
            $cmd->sort = new StdClass;
            foreach ($sort_fields as $field_name) {
                $way = 'desc';
                if (preg_match('#>$#', $field_name)) {
                    $way = 'desc';
                    $field_name = substr($field_name, 0, -1);
                } elseif (preg_match('#<$#', $field_name)) {
                    $way = 'asc';
                    $field_name = substr($field_name, 0, -1);
                }
                if (array_key_exists($field_name, $filter_fields) and $filter_fields[$field_name] !== '') {
                    // 如果有對應的 filterFields，就用 filterFields
                    $es_field_name = $filter_fields[$field_name];
                } else {
                    // 沒有對應的就用 getFieldMap
                    $es_field_name = LYAPI_Type::run($type, 'reverseField', [$field_name]);
                }
                $cmd->sort->{$es_field_name} = $way;
            }
            $records->sort = $sort_fields;
        }

        foreach ($filter_fields as $field_name => $es_field_name) {
            if (self::getParams($field_name)) {
                if (!is_null($output_fields)) {
                    $output_fields[] = $field_name;
                }
                if ($es_field_name === '') {
                    $es_field_name = LYAPI_Type::run($type, 'reverseField', [$field_name]);
                }
                $v = self::getParams($field_name);
                if (is_array($v) and count($v) and is_array($v[0])) {
                    $v = $v[0];
                    $records->range = $records->range ?? new StdClass;
                    $records->range->{$field_name} = $v;

                    $range_obj = [];
                    if ($v[0]) {
                        $range_obj['gte'] = $v[0];
                    }
                    if ($v[1]) {
                        $range_obj['lte'] = $v[1];
                    }
                    $cmd->query->bool->must[] = (object)[
                        'range' => (object)[
                            $es_field_name  => $range_obj,
                        ],
                    ];
                } else {
                    $records->filter->{$field_name} = self::getParams($field_name);
                    $cmd->query->bool->must[] = (object)[
                        'terms' => (object)[
                            $es_field_name => $records->filter->{$field_name},
                        ],
                    ];
                }
            }
        }

        if (self::getParams('q')) {
            $records->query = new StdClass;
            $records->query->q = self::getParam('q');
            $default_query_fields = LYAPI_Type::run($type, 'queryFields');
            if (self::getParams('query_fields')) {
                $query_fields = self::getParams('query_fields');
                foreach ($query_fields as $f) {
                    if (!in_array($f, $default_query_fields)) {
                        throw new Exception(sprintf("query_fields 不支援 %s（支援：%s）", $f, implode(',', $default_query_fields)));
                    }
                }
            } else {
                $query_fields = $default_query_fields;
            }
            $records->query->fields = $query_fields;
            $query_fields = array_map(function ($v) use ($type) {
                return LYAPI_Type::run($type, 'reverseField', [$v]);
            }, $query_fields);
            $cmd->query->bool->must[] = (object)[
                'query_string' => (object)[
                    'query' => self::getParam('q'),
                    'fields' => $query_fields,
                ],
            ];
            $cmd->highlight = (object)[
                'fields' => (object)[
                    '*' => (object)[
                        'pre_tags' => ['<em>'],
                        'post_tags' => ['</em>'],
                    ],
                ],
            ];
        }

        if (self::getParams('agg')) {
            $cmd->aggs = new StdClass;
            foreach (self::getParams('agg') as $agg) {
                $agg_name = strval($agg);
                $agg_fields = explode(',', $agg);
                $agg_term = null;
                while (count($agg_fields)) {
                    $agg_field = array_pop($agg_fields);
                    if (!array_key_exists($agg_field, $filter_fields)) {
                        throw new Exception(sprintf("agg 不支援 %s（支援：%s）", $agg_field, implode(',', array_keys($filter_fields))));
                    }
                    if ($filter_fields[$agg_field] === '') {
                        $es_field = LYAPI_Type::run($type, 'reverseField', [$agg_field]);
                    } else {
                        $es_field = $filter_fields[$agg_field];
                    }
                    if (is_null($agg_term)) {
                        $agg_term = (object)[
                            'terms' => (object)[
                                'field' => $es_field,
                                'size' => 100,
                            ],
                        ];
                    } else {
                        $agg_term = (object)[
                            'terms' => (object)[
                                'field' => $es_field,
                                'size' => 100,
                            ],
                            'aggs' => (object)[
                                $agg_field => $agg_term,
                            ],
                        ];

                    }
                }
                $cmd->aggs->{$agg_name} = $agg_term;
            }
        }

        if (!is_null($output_fields)) {
            $output_fields = array_values(array_unique($output_fields));
            $records->output_fields = $output_fields;
            $cmd->_source = array_map(function($k) use ($type) {
                return LYAPI_Type::run($type, 'reverseField', [$k]);
            }, $output_fields);
        }

        $obj = Elastic::dbQuery("/{prefix}{$type}/_search", 'GET', json_encode($cmd));
        $records->total = $obj->hits->total->value;
        if ($records->limit) {
            $records->total_page = ceil($records->total / $records->limit);
        }
        $return_key = LYAPI_Type::run($type, 'getReturnKey');
        $records->{$return_key} = [];
        // 先掃一遍所有資料，把有在 aggMap 的欄位的 ID 儲存起來
        LYAPI_Type::run($type, 'checkHitRecords', [$obj->hits->hits]);
        foreach ($obj->hits->hits as $hit) {
            $records->{$return_key}[] = LYAPI_Type::run($type, 'buildData', [$hit->_source, $hit->_id, true, $hit->highlight ?? null]);
        }
        if (self::getParams('agg')) {
            $records->aggs = [];
            foreach (self::getParams('agg') as $agg) {
                $agg_name = strval($agg);
                $agg_fields = explode(',', $agg);
                for ($i = 0; $i < count($agg_fields); $i ++) {
                    $sub_agg_name = implode(',', array_slice($agg_fields, 0, $i + 1));

                    $records->aggs[$sub_agg_name] = new StdClass;
                    $records->aggs[$sub_agg_name]->agg = $sub_agg_name;
                    $records->aggs[$sub_agg_name]->agg_fields = array_slice($agg_fields, 0, $i + 1);
                    $records->aggs[$sub_agg_name]->buckets = self::getBuckets(
                        $obj->aggregations->{$agg_name}->buckets, $agg_fields, $i, $type);
                    usort($records->aggs[$sub_agg_name]->buckets, function($a, $b) {
                        return $b->count - $a->count;
                    });
                    $records->aggs[$sub_agg_name]->buckets = array_slice($records->aggs[$sub_agg_name]->buckets, 0, 100);
                }
            }
            $records->aggs = array_values($records->aggs);
        }

        $records->supported_filter_fields = array_keys($filter_fields);
        return $records;
    }

    public static function getItem($type, $ids, $sub, $query_string)
    {
        $id = implode('-', $ids);
        $id = rawurlencode($id);
        $obj = Elastic::dbQuery("/{prefix}{$type}/_doc/{$id}", 'GET');
        if ($obj->found === false) {
            $records = new StdClass;
            $records->error = true;
            $records->message = '找不到資料';
            return $records;
        }
        $relations = LYAPI_Type::run($type, 'getRelations');
        if ($sub[0] ?? false) {
            if (!array_key_exists($sub[0], $relations)) {
                $records = new StdClass;
                $records->error = true;
                $records->message = '找不到子資料';
                return $records;
            }
            $rel = $relations[$sub[0]];
            $query_string = explode('&', $query_string);
            foreach (($rel['map'] ?? []) as $source_key => $target_key) {
                $source_key = LYAPI_Type::run($type, 'reverseField', [$source_key]);
                $target_key = urlencode($target_key);
                if ($source_key == '_id') {
                    $query_string[] = "{$target_key}=" . urlencode($obj->_id);
                } else {
                    $query_string[] = "{$target_key}=" . urlencode($obj->_source->{$source_key});
                }
            }
            $query_string = implode('&', $query_string);
            if ($rel['type'] == '_function') {
                $data = LYAPI_Type::run($type, 'buildData', [$obj->_source, $obj->_id, false]);
                return LYAPI_Type::run($type, $rel['function'], [$data]);
            }
            return self::getCollections($rel['type'], $query_string);
        }
        $records = new StdClass;
        $records->error = false;
        $records->id = $ids;
        $records->data = LYAPI_Type::run($type, 'buildData', [$obj->_source, $obj->_id, false]);
        $records->supported_relations = LYAPI_Type::run($type, 'getRelations');
        $records->relations = [];
        foreach ($records->supported_relations as $k => $v) {
            $id = implode('-', array_map('urlencode', $ids));
            $records->relations[] = [
                'url' => sprintf("https://%s/%s/%s/%s",
                    $_SERVER['HTTP_HOST'], $type, $id, $k),
                'name' => $k,
            ];
        }

        return $records;
    }

    public static function mergeBucketByLevel($es_buckets, $agg_fields, $level, $values, $type)
    {
        $buckets = [];
        $agg_field = array_shift($agg_fields);
        foreach ($es_buckets as $es_bucket) {
            $values[$agg_field] = $es_bucket->key;
            LYAPI_Type::run($type, 'addAggValue', [$agg_field, $es_bucket->key]);
            if ($es_bucket->key_as_string ?? false) {
                $values[$agg_field] = $es_bucket->key_as_string;
            }
            if ($level) {
                $buckets = array_merge($buckets,
                    self::mergeBucketByLevel(
                        $es_bucket->{$agg_field}->buckets, $agg_fields, $level - 1, $values, $type
                    )
                );
            } else {
                $bucket = new StdClass;
                foreach ($values as $k => $v) {
                    $bucket->{$k} = $v;
                }
                $bucket->count = $es_bucket->doc_count;
                $buckets[] = $bucket;
            }
        }
        return $buckets;
    }

    public static function getBuckets($es_buckets, $agg_fields, $level, $type)
    {
        $buckets = self::mergeBucketByLevel($es_buckets, $agg_fields, $level, [], $type);
        $buckets = LYAPI_Type::run($type, 'handleAggValue', [$buckets, $agg_fields, $level]);
        return $buckets;
    }
}
