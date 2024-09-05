<?php

class LYAPI_Type_Ivod extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return [
            'id' => 'IVOD_ID',
            'url' => 'IVOD_URL',
            'date' => '日期',
            'meet' => '會議資料',
            'meet.id' => '會議代碼',
            'meet.term' => '屆',
            'meet.sessionPeriod' => '會期',
            'meet.sessionTimes' => '會次',
            'meet.tmpMeeting' => '臨時會會次',
            'meet.type' => '種類',
            'meet.committees' => '委員會代碼',
            'meet.title' => '標題',
            'type' => '影片種類',
            'start_time' => '開始時間',
            'end_time' => '結束時間',
            'duration' => '影片長度',
            'features' => '支援功能',
        ];
    }

    public static function filterFields()
    {
        return [
            '屆' => 'meet.term',
            '會期' => 'meet.sessionPeriod',
            '會議.會議代碼' => 'meet.id.keyword',
            '委員名稱' => '委員名稱.keyword',
            '會議資料.委員會代碼' => '',
            '日期' => '',
            '影片種類' => 'type.keyword',
        ];
    }

    public static function sortFields()
    {
        return [
            '會議時間',
        ];
    }
}
