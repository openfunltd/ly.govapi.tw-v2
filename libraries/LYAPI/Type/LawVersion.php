<?php

class LYAPI_Type_LawVersion extends LYAPI_Type
{
    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/
        //  => https://data.ly.gov.tw/odw/LawNo.pdf 法律編號
        return [
            'law_id' => '法律編號',
            'version_id' => '版本編號',
            'date' => '日期',
            'action' => '動作',
            'history' => '歷程',
            'current' => '現行版本',
        ];
    }

    public static function filterFields()
    {
        return [
            '法律編號' => 'law_id.keyword',
            '版本編號' => 'version_id.keyword',
            '日期' => 'date',
            '動作' => 'action.keyword',
            '歷程.主提案' => 'history.主提案.keyword',
            '歷程.進度' => 'history.進度.keyword',
            '現行版本' => 'current.keyword',
        ];
    }

    public static function aggMap()
    {
        return [
            '法律編號' => ['law', ['法律編號', '名稱']],
        ];
    }

    public static function sortFields()
    {
        return [
            '法律編號',
            '日期<',
        ];
    }

    public static function getRelations()
    {
        return [
            'contents' => [
                'type' => 'law_content',
                'map' => [
                    '版本編號' => '版本編號',
                ],
            ],
        ];
    }
}
