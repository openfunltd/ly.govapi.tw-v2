<?php

class LYAPI_Type_Law extends LYAPI_Type
{
    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/
        //  => https://data.ly.gov.tw/odw/LawNo.pdf 法律編號
        return [
            'id' => '法律編號',
            'type' => '類別',
            'parent' => '母法編號',
            'name' => '名稱',
            'name_other' => '其他名稱',
            'categories' => '主管機關',
            'status' => '法律狀態',
        ];
    }

    public static function getIdFields()
    {
        return ['法律編號'];
    }

    public static function filterFields()
    {
        return [
            '法律編號' => 'id.keyword',
            '類別' => 'type.keyword',
            '母法編號' => 'parent.keyword',
            '法律狀態' => 'status.keyword',
            '主管機關' => 'categories.keyword',
        ];
    }

    public static function queryFields()
    {
        return [
            '名稱',
            '其他名稱',
        ];
    }

    public static function getRelations()
    {
        return [
            'bills' => [
                'type' => 'bill',
                'map' => [
                    '法律編號' => '法律編號',
                ],
            ],
        ];
    }
}
