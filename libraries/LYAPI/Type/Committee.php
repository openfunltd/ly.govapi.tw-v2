<?php

class LYAPI_Type_Committee extends LYAPI_Type
{
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

    public static function getIdFields()
    {
        return ['委員會代號'];
    }


    public static function filterFields()
    {
        return [
            '委員會類別' => 'comtType.keyword',
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
            ],
        ];
    }
}
