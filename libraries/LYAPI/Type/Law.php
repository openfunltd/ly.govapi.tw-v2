<?php

class LYAPI_Type_Law extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '法律';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '法律編號' => [
                'es_field' => 'id.keyword',
                'description' => '法律編號 [例: 09200015]',
                'type' => 'string',
            ],
            '類別' => [
                'es_field' => 'type.keyword',
                'description' => '類別',
                'type' => 'string',
                'enum' => ['母法', '子法'],
            ],
            '母法編號' => [
                'es_field' => 'parent.keyword',
                'description' => '母法編號 [例: 09200]',
                'type' => 'string',
            ],
            '法律狀態' => [
                'es_field' => 'status.keyword',
                'description' => '法律狀態 [例: 現行]',
                'type' => 'string',
            ],
            '主管機關' => [
                'es_field' => 'categories.keyword',
                'description' => '主管機關 [例: 總統府]',
                'type' => 'string',
            ],
            '最新版本.日期' => [
                'es_field' => 'latest_version.date',
                'description' => '最新版本日期 [例: 2024-10-25]',
                'type' => 'string',
            ],
        ];
    }

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
            'latest_version' => '最新版本',
            'latest_version.date' => '日期',
            'latest_version.action' => '動作', 
            'latest_version.version_id' => '版本編號',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '法律編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '09200015',
            ],
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
            'versions' => [
                'type' => 'law_version',
                'map' => [
                    '法律編號' => '法律編號',
                ],
            ],
        ];
    }

    public static function sortFields()
    {
        return [
            '最新版本.日期',
            '法律編號',
        ];
    }
}
