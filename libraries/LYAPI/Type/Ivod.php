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

        ];
    }

    public static function filterFields()
    {
        return [
            '會議.會議代碼' => 'meet.id.keyword',
        ];
    }
}
