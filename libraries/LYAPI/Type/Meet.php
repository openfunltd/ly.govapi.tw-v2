<?php

class LYAPI_Type_Meet extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return [
            'term' => '屆期',
            'meet_id' => '會議代碼',
            'meet_type' => '會議種類',
            'committees' => '委員會代碼',
            'sessionPeriod' => '會期',
            'sessionTimes' => '會次',
            'dates' => '日期',
            'title' => '會議標題',
            'meet_data' => '會議資料',
            "meet_data.term" => '屆期',
            'meet_data.sessionPeriod' => '會期',
            'meet_data.attendLegislator' => '出席立委',
            'meet_data.meetingNo' => '會議編號',
        ];
    }

    public static function filterFields()
    {
        return [
            '屆期' => '',
            '會期' => '',
            '會議種類' => 'meet_type.keyword',
            '會議資料.出席立委' => 'meet_data.attendLegislator.keyword',
            '日期' => '',
            '委員會代碼' => '',
            '會議編號' => 'meet_data.meetingNo.keyword',
        ];
    }

}
