<?php

class LYAPI_Type_LawContent extends LYAPI_Type
{
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

    public static function getIdFields()
    {
        return ['法條編號'];
    }

    public static function filterFields()
    {
        return [
            '法律編號' => 'law_id.keyword',
            '版本編號' => 'version_id.keyword',
            '順序' => '',
            '條號' => 'rule_no.keyword',
            '現行版' => 'current',
            '版本追蹤' => 'version_trace',
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
