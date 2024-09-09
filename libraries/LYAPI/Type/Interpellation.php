<?php

class LYAPI_Type_Interpellation extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return [
            'term' => '屆',
            'legislators' => '質詢委員',
            'meet_id' => '會議代碼',
            'id' => '質詢編號',
            'page_start' => '質詢起始頁',
            'page_end' => '質詢結束頁',
            'printed_at' => '刊登日期',
            'reason' => '事由',
            'description' => '說明',
            'meetingNo' => '會議編號',
            'sessionPeriod' => '會期',
            'sessionTimes' => '會次',
            'meetingDate' => '會議日期',
        ];
    }

    public static function getIdFields()
    {
        return ['質詢編號'];
    }

    public static function filterFields()
    {
        return [
            '質詢委員' => 'legislators.keyword',
            '屆' => '',
            '會期' => 'sessionPeriod',
            '會議代碼' => 'meet_id.keyword',
        ];
    }

    public static function aggMap()
    {
        return [
            '會議代碼' => ['meet', ['會議代碼', '會議標題']],
        ];
    }

    public static function searchFields()
    {
        return [
            '事由',
            '說明',
        ];
    }
}
