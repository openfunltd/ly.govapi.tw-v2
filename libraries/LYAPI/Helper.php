<?php

class LYAPI_Helper
{
    public static function getApiType($uri)
    {
        $url_terms = explode('/', trim($uri, '/'));
        if ($url_terms[0] == '') {
            return null;
        }
        $type = array_shift($url_terms);

        // 如果 s 結尾，先試試看去掉 s 找找看（TODO: 之後需要處理不規則複數）
        if (substr($type, -1) == 's') {
            $tmp_type = substr($type, 0, -1);
            if (file_exists(__DIR__ . '/Type/' . ucfirst($tmp_type) . '.php')) {
                return ['api', 'collections', [$tmp_type]];
            }
        }

        if (file_exists(__DIR__ . '/Type/' . ucfirst($type) . '.php')) {
            $class = 'LYAPI_Type_' . ucfirst($type);
            $id_term_count = call_user_func([$class, 'get_id_count']);
            $id = array_slice($url_terms, 0, $id_term_count);
            $url_terms = array_slice($url_terms, $id_term_count);
            return ['api', 'item', [$type, $id, $url_terms]];
        }

        return null;
    }
}
