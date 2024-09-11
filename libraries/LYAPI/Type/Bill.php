<?php

class LYAPI_Type_Bill extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return [
            '屆期' => '屆',
            'billNo' => '議案編號',
            'meet_id' => '會議代碼',
            'mtime' => '資料抓取時間',
            'first_time' => '提案日期',
            'last_time' => '最新進度日期',
            'laws' => '法律編號',
        ];
    }

    public static function getIdFields()
    {
        return ['議案編號'];
    }

    public static function filterFields()
    {
        return [
            '屆' => '',
            '會期' => '',
            '議案流程.狀態' => '議案流程.狀態.keyword',
            '議案類別' => '議案類別.keyword',
            '提案人' => '提案人.keyword',
            '連署人' => '連署人.keyword',
            '法律編號' => 'laws.keyword',
            '議案狀態' => '議案狀態.keyword',
            '會議代碼' => 'meet_id.keyword',
            '提案來源' => '提案來源.keyword',
            '議案編號' => 'BillNo.keyword',
            '提案編號' => '提案編號.keyword',
        ];
    }

    public static function aggMap()
    {
        return [
            '法律編號' => ['law', ['法律編號', '名稱']],
            '會議代碼' => ['meet', ['會議代碼', '會議標題']],
        ];
    }

    public static function queryFields()
    {
        return [
            '議案名稱',
            '提案單位/提案委員',
            '提案人',
            '連署人',
            '案由',
            '說明',
        ];
    }

    public static function outputFields()
    {
        return [
            '議案編號',
            '相關附件',
            '議案名稱',
            '提案單位/提案委員',
            '議案狀態',
            '資料抓取時間',
            '屆',
            '議案類別',
            '提案來源',
            '會議代碼',
            '會期',
            '字號',
            '提案編號',
        ];
    }

    public static function sortFields()
    {
        return [
            '資料抓取時間',
        ];
    }

    public static function customData($data, $id)
    {
        $data->url = sprintf("https://ppg.ly.gov.tw/ppg/bills/%s/details", urlencode($data->議案編號));
        return $data;
    }
}
