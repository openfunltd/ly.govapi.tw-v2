<?php

class LYAPI_Type_Meet extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return [
            'term' => '屆',
            'meet_id' => '會議代碼',
            'meet_type' => '會議種類',
            'committees' => '委員會代號',
            'sessionPeriod' => '會期',
            'sessionTimes' => '會次',
            'dates' => '日期',
            'title' => '會議標題',
            'meet_data' => '會議資料', // from https://data.ly.gov.tw/getds.action?id=42
            "meet_data.term" => '屆',
            'meet_data.sessionPeriod' => '會期',
            'meet_data.sessionTimes' => '會次',
            'meet_data.meetingTimes' => '臨時會會次',
            'meet_data.meetingNo' => '會議編號',
            'meet_data.meetingDateDesc' => '會議時間區間',
            'meet_data.meetingRoom' => '會議地點',
            'meet_data.meetingUnit' => '會議單位',
            'meet_data.jointCommittee' => '聯席委員會',
            'meet_data.meetingName' => '會議名稱',
            'meet_data.meetingContent' => '會議事由',
            'meet_data.coChairman' => '委員會召集委員',
            'meet_data.attendLegislator' => '出席委員',
            'meet_data.selectTerm' => '屆別期別篩選條件',
            'meet_data.date' => '日期',
            'meet_data.startTime' => '開始時間',
            'meet_data.endTime' => '結束時間',
        ];
    }

    public static function filterFields()
    {
        return [
            '屆' => '',
            '會期' => '',
            '會議種類' => 'meet_type.keyword',
            '會議資料.出席立委' => 'meet_data.attendLegislator.keyword',
            '日期' => '',
            '委員會代號' => '',
            '會議編號' => 'meet_data.meetingNo.keyword',
        ];
    }

    public static function queryFields()
    {
        return [
            '會議標題',
            '會議資料.會議名稱',
            '會議資料.會議事由',
        ];
    }

    public static function sortFields()
    {
        return [
            '屆',
            '會期',
            '日期',
        ];
    }

    public static function getRelations()
    {
        return [
            'ivod' => [
                'type' => 'ivod',
                'map' => [
                    '會議代碼' => '會議.會議代碼',
                ],
            ]
        ];
    }
}
