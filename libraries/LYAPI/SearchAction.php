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
            return;
        }
        self::$_params = array_map(function($t) {
            return array_map('urldecode', explode('=', $t, 2));
        }, explode('&', $query_string));
    }

    public static function getCollections($type, $query_string)
    {
        self::setQueryString($query_string);
        $cmd = new StdClass;
        $cmd->query = new StdClass;
        $cmd->query->bool = new StdClass;
        $cmd->query->bool->must = [];

        $records = new StdClass;
        $records->total = 0;
        $records->total_page = 0;
        $records->page = intval(self::getParam('page', 1));
        $records->limit = intval(self::getParam('limit', 100));
        $cmd->size = $records->limit;
        $cmd->from = ($records->page - 1) * $records->limit;

        $filter_fields = [
            'term' => 'term',
        ];

        foreach ($filter_fields as $k => $v) {
            if (self::getParams($k)) {
                $records->{$k} = self::getParams($k, ['array' => true]);
                $cmd->query->bool->must[] = (object)[
                    'terms' => (object)[
                        $v => $records->{$k},
                    ],
                ];
            }
        }

        $obj = Elastic::dbQuery("/{prefix}{$type}/_search", 'GET', json_encode($cmd));
        $records->total = $obj->hits->total;
        if ($records->limit) {
            $records->total_page = ceil($records->total->value / $records->limit);
        }
        $return_key = LYAPI_Type::run($type, 'getReturnKey');
        $records->{$return_key} = [];
        foreach ($obj->hits->hits as $hit) {
            $records->{$return_key}[] = $hit->_source;
        }
        $records->supported_filter_fields = array_keys($filter_fields);
        return $records;
    }

    public static function getItem($type, $ids, $sub, $query_string)
    {
        $obj = Elastic::dbQuery("/{prefix}{$type}/_doc/{$ids[0]}", 'GET');
        $records = new StdClass;
        $records->error = false;
        $records->id = $ids;
        $records->data = $obj->_source;
        return $records;
    }
}
