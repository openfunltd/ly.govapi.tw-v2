<?php

class LYAPI_Helper
{
    public static function ucfirst($str)
    {
        // bill => Bill
        // gazette_agenda => GazetteAgenda
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    // 網址用的複數型：不規則的列在這裡，其他直接加 s（type 單數名 => 網址複數名）
    protected static $irregular_plurals = [
        'speech' => 'speeches',
    ];

    public static function plural($singular)
    {
        return self::$irregular_plurals[$singular] ?? ($singular . 's');
    }

    public static function singular($plural)
    {
        $singular = array_search($plural, self::$irregular_plurals);
        if ($singular !== false) {
            return $singular;
        }
        return substr($plural, -1) == 's' ? substr($plural, 0, -1) : $plural;
    }

    public static function getApiType($uri)
    {
        $url_terms = explode('/', trim($uri, '/'));
        if ($url_terms[0] == '') {
            return null;
        }
        $type_underscore = array_shift($url_terms);

        // 去掉結尾 s 的型式，不規則複數（speeches）查表
        $type_underscore_singular = self::singular($type_underscore);

        // 檔名是大駝峰
        $type_pascal = self::ucfirst($type_underscore_singular);

        // 如果 s 結尾且後面沒有參數了，就是 collection
        if (0 == count($url_terms) and substr($type_underscore, -1) == 's') {
            if (file_exists(__DIR__ . "/Type/{$type_pascal}.php")) {
                return ['api', 'collections', [$type_underscore_singular]];
            }
        }

        // 如果大駝峰檔案存在就是 item，網址是 laws/123 或 law/123 都可以對應到 Law.php
        if (file_exists(__DIR__ . "/Type/{$type_pascal}.php")) {
            $class = "LYAPI_Type_{$type_pascal}";
            $id_term_count = count(LYAPI_Type::run($type_underscore_singular, 'getIdFields'));
            $id = array_map('urldecode', array_slice($url_terms, 0, $id_term_count));
            $url_terms = array_slice($url_terms, $id_term_count);
            return ['api', 'item', [$type_underscore_singular, $id, $url_terms]];
        }

        return null;
    }
}
