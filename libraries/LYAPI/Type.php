<?php

class LYAPI_Type
{
    /**
     * 取得所有的 endpoint type，有列出的才會被產生 swagger
     */
    public static function getEndpointTypes()
    {
        return ['list', 'item'];
    }

    /**
     * run 依照 $type 執行對應的 $method，Ex: LYAPI_Type::run('user', 'get', [1]) => LYAPI_Type_User::get(1)
     */
    public static function run($type, $method, $args = [])
    {
        $class = 'LYAPI_Type_' . LYAPI_Helper::ucfirst($type);
        if (!class_exists($class)) {
            throw new Exception('Type not found: ' . $type);
        }
        return call_user_func_array([$class, $method], $args);
    }

    /**
     * 取得 $type 的中文名稱
     */
    public static function getTypeSubject()
    {
        return '';
    }


    /**
     * 取得 filter 參數的欄位資訊，包含 es_field, description, type, enum 等等
     */
    public static function getFilterFieldsInfo(): array
    {
        return [
            /* 例:
            '屆' => [
                'es_field' => '',
                'description' => '議案所屬屆期 [例: 11]',
                'type' => 'integer',
            ],
            '影片種類' => [
                'es_field' => '',
                'description' => '影片種類',
                'type' => 'string',
                'enum' => ['Clip', 'Full'],
            ],
            */
        ];
    }

    /**
     * 回傳 collection api 時，存放資料的 key，預設為 User => users
     */
    public static function getReturnKey()
    {
        return strtolower(str_replace('LYAPI_Type_', '', get_called_class())) . 's';
    }

    /**
     * 回傳 ID 欄位的資訊，包含 type, example 等等
     */
    public static function getIdFieldsInfo()
    {
        return [
            /* 例:
            '議案編號' => [
                'path_name' => 'id',  // 顯示在文件中 path 參數的名稱，不支援中文
                'type' => 'string',
                'example' => '1111102070100100',
            ],
             */
        ];
    }

    /**
     * 回傳預設的 ID 欄位，可指定多筆，例如 legislator 為 ["屆","姓名"]
     */
    public static function getIdFields()
    {
        return array_keys(static::getIdFieldsInfo());
    }

    /**
     * 指定 API 的 field 與 elastic search 的 field 對應
     */
    public static function getFieldMap()
    {
        return new StdClass;
    }

    /**
     * 指定哪些 field 可以支援 filter 和 aggregation，以及對應的 field name
     */
    public static function filterFields()
    {
        $fields = [];
        foreach (static::getFilterFieldsInfo() as $field => $info) {
            $fields[$field] = $info['es_field'];
        }
        return $fields;
    }

    /**
     * 指定使用 q 參數搜尋時，不指定 query_fields 時，預設搜尋哪些欄位
     */
    public static function queryFields()
    {
        return [];
    }

    /**
     * 在使用 collection api 時，不指定 output_fields={field_name} 時輸出哪些欄位，預設為全部
     */
    public static function outputFields()
    {
        return [];
    }

    /**
     * 預設的排序欄位
     */
    public static function sortFields()
    {
        return [];
    }

    /**
     * 預設的 collection api 回傳資料筆數，可被 limit 參數覆寫
     */
    public static function defaultLimit()
    {
        return 100;
    }

    /**
     * 支援透過 /{type}/{id}/{relation_name} 可以連結到其他 collection 的資料
     */
    public static function getRelations()
    {
        return [];
    }

    /**
     * 支援在 aggregation 時，可將一些代碼型的欄位對應成他的值
     */
    public static function aggMap()
    {
        return [];
    }

    /**
     * 利用 $field_map ，將單筆資料的 elastic 的資料欄位改名成 api 輸出的欄位
     */
    public static function filterData($origin_data, $field_map, $es_prefix, $output_prefix, $only_check_agg_map = false)
    {
        if (is_array($origin_data)) {
            $data = array_map(function ($v) use ($field_map, $es_prefix, $output_prefix, $only_check_agg_map) {
                return self::filterData($v, $field_map, $es_prefix, $output_prefix, $only_check_agg_map);
            }, $origin_data);
            return $data;
        }
        if (!is_object($origin_data)) {
            return $origin_data;
        }

        $data = new StdClass;
        $outputed = [];
        foreach ($field_map as $es_key => $output_key) {
            if ($es_prefix and strpos($es_key, $es_prefix) !== 0) {
                continue;
            } elseif (!$es_prefix and strpos($es_key, '.') !== false) {
                continue;
            }
            $es_key = substr($es_key, strlen($es_prefix));
            if (property_exists($origin_data, $es_key)) {
                $outputed[$es_key] = true;
                $data->{$output_key} = self::filterData(
                    $origin_data->{$es_key},
                    $field_map,
                    rtrim($es_prefix . $es_key, '.') . '.',
                    rtrim($output_prefix . $output_key, '.') . '.',
                    $only_check_agg_map
                );
                if ($only_check_agg_map) {
                    static::addAggValue(rtrim($output_prefix . $output_key, '.'), $origin_data->{$es_key});
                } else {
                    $v_str = self::getAggValueMap(rtrim($output_prefix . $output_key, '.'), $origin_data->{$es_key}, get_called_class());
                    if ($v_str) {
                        $data->{$output_key . ':str'} = $v_str;
                    }
                }
            }
        }
        foreach ($origin_data as $k => $v) {
            if (array_key_exists($k, $outputed)) {
                continue;
            }
            $data->{$k} = self::filterData($v, $field_map, $es_prefix, $output_prefix, $only_check_agg_map);
        }
        return $data;
    }

    /**
     * 處理將 elastic 的資料轉換成 api 輸出的格式
     */
    public static function buildData($data, $id, $in_collection = false, $highlight = null)
    {
        $field_map = static::getFieldMap();
        foreach ($field_map as $k => $v) {
            if (strpos($k, '.') === false) {
                continue;
            }
            $prefix = implode('.', array_slice(explode('.', $k), 0, -1));
            if (!array_key_exists($prefix, $field_map)) {
                $field_map[$prefix] = array_pop(explode('.', $prefix));
            }
        }
        if (array_key_exists('_id', $field_map)) {
            $data->{$field_map['_id']} = $id;
        }
        if ($in_collection) {
            $data = self::filterData($data, $field_map, '', '', false);
        } else {
            $checking_data = clone $data;
            self::filterData($checking_data, $field_map, '', '', true);
            $data = self::filterData($data, $field_map, '', '', false);
        }
        if (!is_null($highlight)) {
            $highlight = self::filterData($highlight, $field_map, '', '', false);
            foreach ($highlight as $k => $v) {
                $data->{$k . ':highlight'} = $v;
            }
        }
        $data = static::customData($data, $id);
        return $data;
    }

    /**
     * 給各 type 可以自定義的資料處理，預設為不處理
     */
    public static function customData($data, $id)
    {
        return $data;
    }

    protected static $_reverse_field_map = [];

    /**
     * 將 api 輸入的 field name 轉換成 elastic search 的 field name
     */
    public static function reverseField($field)
    {
        $reverse_field_map = static::getReverseFieldMap();
        return $reverse_field_map[$field] ?? $field;
    }

    /**
     * 計算 api 輸入的 field name 轉換成 elastic search 的 field name 的對照表
     */
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

                    if (!array_key_exists($p, $prefix)) {
                        $prefix[$p] = array_shift(explode('.', $p));
                    }
                    $v = $prefix[$p] . '.' . $v;
                }
                self::$_reverse_field_map[$class][$v] = $k;
            }
        }
        return self::$_reverse_field_map[$class];
    }

    protected static $_agg_values = [];
    protected static $_agg_values_result = [];
    /**
     * 記錄起來 aggMap() 可能會用到的值，讓之後可以一次計算對應表
     */
    public static function addAggValue($field, $value)
    {
        $agg_map = static::aggMap();

        if (!array_key_exists($field, $agg_map)) {
            return;
        }
        if (!$value) {
            return;
        }
        if (is_array($value)) {
            foreach ($value as $v) {
                static::addAggValue($field, $v);
            }
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

    /**
     * 一次搜尋 addAggValue() 記錄的值的對應
     */
    public static function termSearch($class, $field, $type, $config)
    {
        $terms = array_keys(self::$_agg_values[$class][$field]);
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

    /**
     * 取得某個在 aggMap 的欄位對應後的值
     */
    public static function getAggValueMap($field, $value, $class)
    {
        $agg_map = static::aggMap();

        if (!array_key_exists($field, $agg_map)) {
            return null;
        }
        if (!array_key_exists($class, self::$_agg_values_result)) {
            return null;
        }
        if (is_null($value)) {
            return null;
        }
        $map = $agg_map[$field];
        list($type, $config) = $map;
        if (is_array($value)) {
            $value = array_map(function ($v) use ($field) {
                return self::getAggValueMap($field, $v, get_called_class());
            }, $value);
            return $value;
        }
        if ($type == '_function') {
            return call_user_func($config, $value);
        }
        if (!array_key_exists($value, self::$_agg_values_result[$class][$field])) {
            self::termSearch($class, $field, $type, $config);
        }
        return self::$_agg_values_result[$class][$field][$value];
    }

    /**
     * 處理 buckets 的值，將 aggMap 裡的值轉換成對應的值
     */
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

    public static function checkHitRecords($hits)
    {
        $agg_map = static::aggMap();
        if (!$agg_map) {
            return $hits;
        }
        $field_map = static::getFieldMap();
        foreach ($field_map as $k => $v) {
            if (strpos($k, '.') === false) {
                continue;
            }
            $prefix = implode('.', array_slice(explode('.', $k), 0, -1));
            if (!array_key_exists($prefix, $field_map)) {
                $field_map[$prefix] = array_pop(explode('.', $prefix));
            }
        }
        foreach ($hits as $hit) {
            $source = clone $hit->_source;
            self::filterData($source, $field_map, '', '', true);
        }
    }
}
