<?php

class LYAPI_Type_Gazette extends LYAPI_Type
{
    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/getds.action?id=41
        //comYear：卷, comVolume：期, comBookId：冊別
        return [
            'comYear' => '卷',
            'comVolume' => '期',
            'comBookId' => '冊別',
            'published_at' => '發布日期',
            '_id' => '公報編號',
        ];
    }

    public static function filterFields()
    {
        return [
            '公報編號' => '',
            '卷' => '',
        ];
    }
}
