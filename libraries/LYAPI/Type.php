<?php

class LYAPI_Type
{
    public static function run($type, $method, $args = [])
    {
        $class = 'LYAPI_Type_' . ucfirst($type);
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

    public static function buildData($data)
    {
        $field_map = static::getFieldMap();
        $data = self::filterData($data, $field_map, '');
        return $data;
    }
}
