<?php

class LYAPI_Type_Ivod extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return 'IVOD';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '屆' => [
                'es_field' => 'meet.term',
                'description' => '屆 [例: 11]',
                'type' => 'integer',
            ],
            '會期' => [
                'es_field' => 'meet.sessionPeriod',
                'description' => '會期 [例: 2]',
                'type' => 'integer',
            ],
            '會議.會議代碼' => [
                'es_field' => 'meet.id.keyword',
                'description' => '會議.會議代碼 [例: 委員會-11-2-22-5]',
                'type' => 'string',
            ],
            '委員名稱' => [
                'es_field' => '委員名稱.keyword',
                'description' => '委員名稱 [例: 陳培瑜]',
                'type' => 'string',
            ],
            '會議資料.委員會代碼' => [
                'es_field' => '',
                'description' => '會議資料.委員會代碼 [例: 22]',
                'type' => 'integer',
            ],
            '會議資料.會議代碼' => [
                'es_field' => 'meet.id.keyword',
                'description' => '會議資料.會議代碼 [例: 委員會-11-2-22-5]',
                'type' => 'string',
            ],
            '日期' => [
                'es_field' => '',
                'description' => '日期 [例: 2024-10-24]',
                'type' => 'string',
            ],
            '影片種類' => [
                'es_field' => 'type.keyword',
                'description' => '影片種類',
                'type' => 'string',
                'enum' => ['Clip', 'Full'],
            ],
        ];
    }

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

    public static function getIdFieldsInfo()
    {
        return [
            'IVOD_ID' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '156045',
            ],
        ];
    }

    public static function aggMap()
    {
        return [
            '會議資料.委員會代碼' => ['committee', ['委員會代號', '委員會名稱']],
            '會議資料.會議代碼' => ['meet', ['會議代碼', '會議標題']],
        ];
    }

    public static function sortFields()
    {
        return [
            '會議時間',
        ];
    }

    public static function outputFields()
    {
        return [
            'video_url',
            '會議時間',
            '會議名稱',
            '委員名稱',
            '影片長度',
            '委員發言時間',
            'IVOD_ID',
            'IVOD_URL',
            '日期',
            '會議資料',
            '影片種類',
            '開始時間',
            '結束時間',
            '支援功能',
        ];
    }
}
