<?php

class LYAPI_Type_Interpellation extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '質詢';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '質詢委員' => [
                'es_field' => 'legislators.keyword',
                'description' => '質詢委員 [例: 羅智強]',
                'type' => 'string',
            ],
            '屆' => [
                'es_field' => 'term',
                'description' => '屆 [例: 11]',
                'type' => 'integer',
            ],
            '會期' => [
                'es_field' => 'sessionPeriod',
                'description' => '會期 [例: 2]',
                'type' => 'integer',
            ],
            '會議代碼' => [
                'es_field' => 'meet_id.keyword',
                'description' => '會議代碼 [例: 院會-11-2-6]',
                'type' => 'string',
            ],
        ];
    }

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

    public static function getIdFieldsInfo()
    {
        return [
            '質詢編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '11-1-1-1',
            ],
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
