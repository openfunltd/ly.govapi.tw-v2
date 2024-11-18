<?php

class LYAPI_Type_LawContent extends LYAPI_Type
{
    public static function getEnpointGroup()
    {
        return 'Law';
    }

    public static function getTypeSubject()
    {
        return '法條';
    }

    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/
        //  => https://data.ly.gov.tw/odw/LawNo.pdf 法律編號
        return [
            'law_id' => '法律編號',
            'version_id' => '版本編號',
            'idx' => '順序',
            'rule_no' => '條號',
            'content' => '內容',
            'section_name' => '章名',
            'law_content_id' => '法條編號',
            'reason' => '立法理由',
            'current' => '現行版',
            'version_trace' => '版本追蹤',
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
                'description' => '版本編號 [例: 90481:90481:1944-02-29-制定:1]',
                'type' => 'string',
            ],
            '順序' => [
                'es_field' => '',
                'description' => '順序 [例: 1]',
                'type' => 'integer',
            ],
            '條號' => [
                'es_field' => 'rule_no.keyword',
                'description' => '條號 [例: 第一條]',
                'type' => 'string',
            ],
            '現行版' => [
                'es_field' => 'current',
                'description' => '現行版',
                'type' => 'string',
                'enum' => ['現行', '非現行'],
            ],
            '版本追蹤' => [
                'es_field' => 'version_trace',
                'description' => '版本追蹤 [例: new]',
                'type' => 'string',
            ],
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '法條編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '90481:90481:1944-02-29-制定:0',
            ],
        ];
    }

    public static function queryFields()
    {
        return [
            '內容',
            '立法理由',
        ];
    }

    public static function aggMap()
    {
        return [
            '法律編號' => ['law', ['法律編號', '名稱']],
        ];
    }

    public static function defaultLimit()
    {
        return 500;
    }

    public static function sortFields()
    {
        return [
            '法律編號',
            '版本編號<',
            '順序<',
        ];
    }
}
