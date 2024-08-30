<?php

class LYAPI_Type
{
    public static function run($type, $method)
    {
        $class = 'LYAPI_Type_' . ucfirst($type);
        if (!class_exists($class)) {
            throw new Exception('Type not found: ' . $type);
        }
        return call_user_func([$class, $method]);
    }

    public static function getReturnKey()
    {
        return strtolower(str_replace('LYAPI_Type_', '', get_called_class())) . 's';
    }

    public static function get_id_count()
    {
        return 1;
    }
}
