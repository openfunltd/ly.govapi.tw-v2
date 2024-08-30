<?php

class LYAPI_Type_Meet extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return (object)[
            'term' => '屆期',
            'meet_id' => '會議代碼',
            'meet_type' => '會議種類',
            'committees' => '委員會代碼',
            'sessionPeriod' => '會期',
            'sessionTimes' => '會次',
            'title' => '會議標題',
            'meet_data' => '會議資料',
            "meet_data.term" => '屆期',
            'meet_data.sessionPeriod' => '會期',
        ];
    }
}
