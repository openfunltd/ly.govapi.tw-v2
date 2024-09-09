<?php

class LYAPI_Type
{
    public static function run($type, $method, $args = [])
    {
        $class = 'LYAPI_Type_' . LYAPI_Helper::ucfirst($type);
        if (!class_exists($class)) {
            throw new Exception('Type not found: ' . $type);
        }
        return call_user_func_array([$class, $method], $args);
    }

    public static function getReturnKey()
    {
        return strtolower(str_replace('LYAPI_Type_', '', get_called_class())) . 's';
    }

    public static function getIdFields()
    {
        return ['_id'];
    }

    public static function getFieldMap()
    {
        return new StdClass;
    }

    public static function filterFields()
    {
        return [];
    }

    public static function queryFields()
    {
        return [];
    }

    public static function outputFields()
    {
        return [];
    }

    public static function sortFields()
    {
        return [];
    }

    public static function defaultLimit()
    {
        return 100;
    }

    public static function getRelations()
    {
        return [];
    }

    public static function aggMap()
    {
        return [];
    }

    public static function filterData($data, $field_map, $prefix)
    {
        if (is_array($data)) {
            $data = array_map(function ($v) use ($field_map, $prefix) {
                return self::filterData($v, $field_map, $prefix);
            }, $data);
            return $data;
        }
        if (!is_object($data)) {
            return $data;
        }

        foreach ($field_map as $k => $v) {
            if ($prefix) {
                if (strpos($k, $prefix) !== 0) {
                    continue;
                }
                $k = substr($k, strlen($prefix));
            }
            if (property_exists($data, $k)) {
                $data->{$v} = self::filterData($data->{$k}, $field_map, "{$k}.");
                unset($data->{$k});
            }
        }
        return $data;
    }

    public static function buildData($data, $id)
    {
        $field_map = static::getFieldMap();
        if (array_key_exists('_id', $field_map)) {
            $data->{$field_map['_id']} = $id;
        }
        $data = self::filterData($data, $field_map, '');
        $data = static::customData($data, $id);
        return $data;
    }

    public static function customData($data, $id)
    {
        return $data;
    }

    protected static $_reverse_field_map = [];

    public static function reverseField($field)
    {
        $reverse_field_map = static::getReverseFieldMap();
        return $reverse_field_map[$field] ?? $field;
    }

    public static function getReverseFieldMap()
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$_reverse_field_map)) {
            self::$_reverse_field_map[$class] = [];
            $prefix = [];

            $field_map = static::getFieldMap();
            foreach ($field_map as $k => $v) {
                $prefix[$k] = $v;
                if (strpos($k, '.') !== false) {
                    $terms = explode('.', $k);
                    $p = implode('.', array_slice($terms, 0, -1));

                    $v = $prefix[$p] . '.' . $v;
                }
                self::$_reverse_field_map[$class][$v] = $k;
            }
        }
        return self::$_reverse_field_map[$class];
    }

    protected static $_agg_values = [];
    protected static $_agg_values_result = [];
    public static function addAggValue($field, $value)
    {
        $agg_map = static::aggMap();

        if (!array_key_exists($field, $agg_map)) {
            return;
        }
        $class = get_called_class();
        if (!array_key_exists($class, self::$_agg_values)) {
            self::$_agg_values[$class] = [];
            self::$_agg_values_result[$class] = [];
        }
        if (!array_key_exists($field, self::$_agg_values[$class])) {
            self::$_agg_values[$class][$field] = [];
            self::$_agg_values_result[$class][$field] = [];
        }
        self::$_agg_values[$class][$field][$value] = $value;
    }

    public static function termSearch($class, $field, $type, $config)
    {
        $terms = array_keys(self::$_agg_values[$class][$field]);
        error_log("termSearch: {$field} " . json_encode($terms));
        $cmd = new StdClass;
        $cmd->size = count($terms);
        $cmd->query = new StdClass;
        $cmd->query->terms = new StdClass;
        $filter_fields = LYAPI_Type::run($type, 'filterFields');
        if (array_key_exists($config[0], $filter_fields)) {
            $query_field = $filter_fields[$config[0]];
        }
        if (!$query_field) {
            $query_field = LYAPI_Type::run($type, 'reverseField', [$config[0]]);
        }

        $output_field = LYAPI_Type::run($type, 'reverseField', [$config[1]]);
        $cmd->query->terms->{$query_field} = array_values(self::$_agg_values[$class][$field]);
        $query_field = str_replace('.keyword', '', $query_field);
        $output_field = str_replace('.keyword', '', $output_field);
        $cmd->_source = [
            $query_field,
            $output_field,
        ];
        $obj = Elastic::dbQuery("/{prefix}{$type}/_search", 'GET', json_encode($cmd));
        foreach ($obj->hits->hits as $hit) {
            $source = $hit->_source;
            self::$_agg_values_result[$class][$field][$source->{$query_field}] = $source->{$output_field};
            unset(self::$_agg_values[$class][$field][$source->{$query_field}]);
        }
        foreach (self::$_agg_values[$class][$field] as $v) {
            self::$_agg_values_result[$class][$field][$v] = '';
        }
    }

    public static function getAggValueMap($field, $value, $class)
    {
        $agg_map = static::aggMap();
        if (!array_key_exists($field, $agg_map)) {
            return;
        }
        $map = $agg_map[$field];
        list($type, $config) = $map;
        if (!array_key_exists($value, self::$_agg_values_result[$class][$field])) {
            self::termSearch($class, $field, $type, $config);
        }
        return self::$_agg_values_result[$class][$field][$value];
    }

    public static function handleAggValue($buckets, $agg_fields, $level)
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$_agg_values)) {
            return $buckets;
        }

        foreach ($buckets as $idx => $bucket) {
            for ($i = 0; $i <= $level; $i ++) {
                $k = $agg_fields[$i];
                $v = self::getAggValueMap($k, $bucket->{$k}, $class);
                if ($v) {
                    $buckets[$idx]->{$k . ':str'} = $v;
                }
            }
        }
        return $buckets;
    }
}
