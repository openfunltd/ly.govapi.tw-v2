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
        if (trim($query_string) == '') {
            self::$_params = [];
        }
        self::$_params = array_map(function($t) {
            return array_map('urldecode', explode('=', $t, 2));
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

        $default_sort_fields = LYAPI_Type::run($type, 'sortFields');
        $sort_fields = $default_sort_fields;
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
                $cmd->sort->{LYAPI_Type::run($type, 'reverseField', [$field_name])} = $way;
            }
            $records->sort = $sort_fields;
        }

        $filter_fields = LYAPI_Type::run($type, 'filterFields');

        foreach ($filter_fields as $field_name => $v) {
            if (self::getParams($field_name)) {
                if (!is_null($output_fields)) {
                    $output_fields[] = $field_name;
                }
                if ($v === '') {
                    $v = LYAPI_Type::run($type, 'reverseField', [$field_name]);
                }
                $records->filter->{$field_name} = self::getParams($field_name, ['array' => true]);
                $cmd->query->bool->must[] = (object)[
                    'terms' => (object)[
                        $v => $records->filter->{$field_name},
                    ],
                ];
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
        foreach ($obj->hits->hits as $hit) {
            $records->{$return_key}[] = LYAPI_Type::run($type, 'buildData', [$hit->_source, $hit->_id]);
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
                        $obj->aggregations->{$agg_name}->buckets, $agg_fields, $i);
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
        $obj = Elastic::dbQuery("/{prefix}{$type}/_doc/{$id}", 'GET');
        if ($obj->found === false) {
            $records = new StdClass;
            $records->error = true;
            $records->message = '找不到資料';
            return $records;
        }
        $relations = LYAPI_Type::run($type, 'getRelations');
        if ($sub[0]) {
            if (!array_key_exists($sub[0], $relations)) {
                $records = new StdClass;
                $records->error = true;
                $records->message = '找不到子資料';
                return $records;
            }
            $rel = $relations[$sub[0]];
            $query_string = explode('&', $query_string);
            foreach ($rel['map'] as $source_key => $target_key) {
                $source_key = LYAPI_Type::run($type, 'reverseField', [$source_key]);
                $target_key = urlencode($target_key);
                $query_string[] = "{$target_key}=" . urlencode($obj->_source->{$source_key});
            }
            $query_string = implode('&', $query_string);
            return self::getCollections($rel['type'], $query_string);
        }
        $records = new StdClass;
        $records->error = false;
        $records->id = $ids;
        $records->data = LYAPI_Type::run($type, 'buildData', [$obj->_source, $hit->_id]);
        return $records;
    }

    public static function mergeBucketByLevel($es_buckets, $agg_fields, $level, $values)
    {
        $buckets = [];
        $agg_field = array_shift($agg_fields);
        foreach ($es_buckets as $es_bucket) {
            $values[$agg_field] = $es_bucket->key;
            if ($es_bucket->key_as_string ?? false) {
                $values[$agg_field] = $es_bucket->key_as_string;
            }
            if ($level) {
                $buckets = array_merge($buckets,
                    self::mergeBucketByLevel(
                        $es_bucket->{$agg_field}->buckets, $agg_fields, $level - 1, $values
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

    public static function getBuckets($es_buckets, $agg_fields, $level)
    {
        return self::mergeBucketByLevel($es_buckets, $agg_fields, $level, []);
    }
}
