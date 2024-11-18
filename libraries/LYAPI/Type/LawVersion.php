<?php

class LYAPI_Type_LawVersion extends LYAPI_Type
{
    public static function getEnpointGroup()
    {
        return 'Law';
    }

    public static function getTypeSubject()
    {
        return '法律版本';
    }

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

    public static function getFilterFieldsInfo(): array
    {
        return [
            '法律編號' => [
                'es_field' => 'law_id.keyword',
                'description' => '法律編號 [例: 90481]',
                'type' => 'string',
            ],
            '版本編號' => [
                'es_field' => 'version_id.keyword',
                'description' => '版本編號 [例: 90481:1944-02-29-制定]',
                'type' => 'string',
            ],
            '日期' => [
                'es_field' => 'date',
                'description' => '日期 [例: 1944-02-29]',
                'type' => 'string',
            ],
            '動作' => [
                'es_field' => 'action.keyword',
                'description' => '動作 [例: 制定]',
                'type' => 'string',
            ],
            '歷程.主提案' => [
                'es_field' => 'history.主提案.keyword',
                'description' => '歷程.主提案 [例: 張子揚]',
                'type' => 'string',
            ],
            '歷程.進度' => [
                'es_field' => 'history.進度.keyword',
                'description' => '歷程.進度 [例: 一讀]',
                'type' => 'string',
            ],
            '現行版本' => [
                'es_field' => 'current.keyword',
                'description' => '現行版本',
                'type' => 'string',
                'enum' => ['現行', '非現行'],
            ],
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '版本編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '90481:1944-02-29-制定',
            ],
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
                'subject' => '法律版本包含的法條',
            ],
        ];
    }
}
