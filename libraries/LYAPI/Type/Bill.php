<?php

class LYAPI_Type_Bill extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return [
            'billNo' => '議案編號',
            'meet_id' => '會議代碼',
            'mtime' => '資料抓取時間',
            'first_time' => '提案日期',
            'last_time' => '最新進度日期',
        ];
    }

    public static function filterFields()
    {
        return [
            '屆期' => '',
            '議案流程.狀態' => '議案流程.狀態.keyword',
            '提案人' => '提案人.keyword',
            '連署人' => '連署人.keyword',
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
            '屆期',
            '議案類別',
            '提案來源',
            '會議代碼',
            '會期',
            '字號',
            '提案編號',
        ];
    }
}
