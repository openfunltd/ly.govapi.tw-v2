<?php

class LYAPI_Type_Committee extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '委員會';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '委員會類別' => [
                'es_field' => 'comtType.keyword',
                'description' => '委員會類別 [例: 常設委員會]',
                'type' => 'string',
            ],
            '委員會代號' => [
                'es_field' => 'comtCd.keyword',
                'description' => '委員會代號 [例: 15]',
                'type' => 'integer',
            ],
        ];
    }

    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/getds.action?id=14
        // comtcd：委員會代號, comtName：委員會名稱, comtDesp：委員會/職掌, comtType：委員會類別(1:常設委員會2:特種委員會3:國會改革前舊委員會名稱)
        return [
            'comtCd' => '委員會代號',
            'comtName' => '委員會名稱',
            'comtDesp' => '委員會職掌',
            'comtType' => '委員會類別',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '委員會代號' => [
                'path_name' => 'comtCd',
                'type' => 'string',
                'example' => '15',
            ],
        ];
    }

    public static function aggMap()
    {
        return [
            '委員會類別' => ['_function', ['LYAPI_Type_Committee', 'aggComtType']],
        ];
    }

    public static function aggComtType($v)
    {
        $map = [
            1 => '常設委員會',
            2 => '特種委員會',
            3 => '國會改革前舊委員會名稱',
        ];
        return $map[$v];
    }

    public static function filterFields()
    {
        return [
            '委員會類別' => 'comtType.keyword',
            '委員會代號' => 'comtCd',
        ];
    }

    public static function getRelations()
    {
        return [
            'meets' => [
                'type' => 'meet',
                'map' => [
                    '委員會代號' => '委員會代號',
                ],
                'subject' => '委員會相關會議',
            ],
        ];
    }
}
