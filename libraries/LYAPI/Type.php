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

    public static function get_id_count()
    {
        return 1;
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
}
