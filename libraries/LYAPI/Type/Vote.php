<?php

class LYAPI_Type_Vote extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '表決';
    }

    public static function getIndexType()
    {
        return 'gazette_vote';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '屆' => [
                'es_field' => 'term',
                'description' => '屆 [例: 11]',
                'type' => 'integer',
            ],
            '會議代碼' => [
                'es_field' => 'meet_id.keyword',
                'description' => '會議代碼 [例: 院會-11-4-14]',
                'type' => 'string',
            ],
            '表決型態' => [
                'es_field' => '表決型態.keyword',
                'description' => '表決型態 [例: 記名表決]',
                'type' => 'string',
            ],
            '表決時間' => [
                'es_field' => '表決時間.keyword',
                'description' => '表決時間 [例: 中華民國114年12月19日 上午10時42分02秒]',
                'type' => 'string',
            ],
            '贊成' => [
                'es_field' => '贊成.keyword',
                'description' => '贊成的委員姓名 [例: 黃國昌]',
                'type' => 'string',
            ],
            '反對' => [
                'es_field' => '反對.keyword',
                'description' => '反對的委員姓名 [例: 黃國昌]',
                'type' => 'string',
            ],
            '棄權' => [
                'es_field' => '棄權.keyword',
                'description' => '棄權的委員姓名 [例: 黃國昌]',
                'type' => 'string',
            ],
            '公報文件代碼' => [
                'es_field' => 'lcidc_doc_id.keyword',
                'description' => '公報文件代碼 [例: 1150101_00002]',
                'type' => 'string',
            ],
        ];
    }

    public static function getFieldMap()
    {
        return [
            'term' => '屆',
            'meet_id' => '會議代碼',
            'lcidc_doc_id' => '公報文件代碼',
            'line_no' => '行號',
            '會議名稱' => '會議名稱',
            '表決型態' => '表決型態',
            '表決時間' => '表決時間',
            '表決議題' => '表決議題',
            '表決結果' => '表決結果',
            '表決結果.出席人數' => '出席人數',
            '表決結果.贊成人數' => '贊成人數',
            '表決結果.反對人數' => '反對人數',
            '表決結果.棄權人數' => '棄權人數',
            '贊成' => '贊成',
            '反對' => '反對',
            '棄權' => '棄權',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '表決代碼' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '1150101_00002_55',
            ],
        ];
    }

    public static function queryFields()
    {
        return [
            '表決議題',
            '會議名稱',
        ];
    }

    public static function sortFields()
    {
        return [
            '屆',
            '表決時間',
        ];
    }

    public static function getRelations()
    {
        return [
            'meets' => [
                'type' => 'meet',
                'map' => [
                    '會議代碼' => '會議代碼',
                ],
                'subject' => '表決所屬的會議',
            ],
        ];
    }
}
