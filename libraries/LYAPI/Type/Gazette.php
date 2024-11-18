<?php

class LYAPI_Type_Gazette extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '公報';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '公報編號' => [
                'es_field' => '',
                'description' => '公報編號 [例: 1137701]',
                'type' => 'string',
            ],
            '卷' => [
                'es_field' => '',
                'description' => '卷 [例: 113]',
                'type' => 'integer',
            ],
        ];
    }

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

    public static function getIdFieldsInfo()
    {
        return [
            '公報編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '1137701',
            ],
        ];
    }

    public static function sortFields()
    {
        return [
            '卷',
            '期',
            '冊別',
        ];
    }

    public static function getRelations()
    {
        return [
            'agendas' => [
                'type' => 'gazette_agenda',
                'map' => [
                    '公報編號' => '公報編號',
                ],
                'subject' => '公報所含的公報目錄',
            ],
        ];
    }
}
