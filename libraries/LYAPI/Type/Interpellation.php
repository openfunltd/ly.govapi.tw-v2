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
                // 同一則質詢可能在連續兩次院會的關係文書都刊登，meet_ids 是全部刊登過的會議；meet_id 是最早那次
                'es_field' => 'meet_ids.keyword',
                'description' => '會議代碼，列入該次院會關係文書的質詢（同一則可能刊登於多次會議） [例: 院會-11-2-6]',
                'type' => 'string',
            ],
            '會次' => [
                'es_field' => 'sessionTimes',
                'description' => '最早刊登的會次 [例: 6]',
                'type' => 'integer',
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
            'no' => '流水號',
            'meet_ids' => '會議代碼列表',
            'listings' => '刊登紀錄',
            'listings.meet_id' => '會議代碼',
            'listings.sessionTimes' => '會次',
            'listings.meetingNo' => '會議編號',
            'listings.printed_at' => '刊登日期',
            'listings.page_start' => '質詢起始頁',
            'listings.page_end' => '質詢結束頁',
            'listings.source_page' => '來源頁面',
            'listings.source_pdf' => '來源PDF',
            'source_page' => '來源頁面',
            'source_pdf' => '來源PDF',
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
                // 屆-會期-流水號（質詢編號每會期連續）；舊的「屆-會期-會次-編號」四段式網址會 301 到新編號
                'example' => '11-1-1',
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
