<?php

/**
 * 公報發言（speech index）：公報逐字稿以「一次發言」為單位的 block。
 * 匯入端：ly.govapi.tw 的 imports/gazette/import-speech.php（切分規則在 GazetteSpeechParser.php）。
 * 公報每個字都在某個 block 裡（附件、議事錄也在），所以 q= 關鍵字搜尋等於公報內文全文搜尋。
 */
class LYAPI_Type_Speech extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '公報發言';
    }

    public static function getReturnKey()
    {
        return 'speeches';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '屆' => [
                'es_field' => 'term',
                'description' => '屆 [例: 11]',
                'type' => 'integer',
            ],
            '會期' => [
                'es_field' => 'sessionPeriod',
                'description' => '會期 [例: 5]',
                'type' => 'integer',
            ],
            '會議代碼' => [
                'es_field' => 'meet_id',
                'description' => '會議代碼 [例: 委員會-11-5-20-18]',
                'type' => 'string',
            ],
            '會議種類' => [
                'es_field' => 'meet_type',
                'description' => '會議種類 [例: 委員會]',
                'type' => 'string',
            ],
            '委員會代號' => [
                'es_field' => 'committees',
                'description' => '委員會代號 [例: 20]',
                'type' => 'integer',
            ],
            '公報編號' => [
                'es_field' => 'gazette_id',
                'description' => '公報編號 [例: 1154904]',
                'type' => 'string',
            ],
            '公報議程編號' => [
                'es_field' => 'agenda_id',
                'description' => '公報議程編號（對應 meet 的公報發言紀錄.agenda_id）[例: 1154904_00003]',
                'type' => 'string',
            ],
            '文件編號' => [
                'es_field' => 'lcidc_id',
                'description' => '公報子文件編號（LCIDC01_{文件編號}.doc）[例: 1154904_00004]',
                'type' => 'string',
            ],
            '日期' => [
                'es_field' => 'date',
                'description' => '會議日期 [例: 2026-06-10]',
                'type' => 'string',
            ],
            '順序' => [
                'es_field' => 'seq',
                'description' => '在公報文件內的順序（從 0 起）；配合 /meet_sections 的 發言起～發言迄 用區間篩：順序:12,30',
                'type' => 'integer',
            ],
            '區塊種類' => [
                'es_field' => 'kind',
                'description' => '區塊種類：發言／主席程序（主席宣布開會、請某人發言等 60 字以內的程序話）／附件（書面質詢、對照表等）／議事錄（上次會議議事錄全文）／宣讀表決／標題（文件開頭的時間地點主席）',
                'type' => 'string',
                'enum' => ['發言', '主席程序', '附件', '議事錄', '宣讀表決', '標題'],
            ],
            '發言者' => [
                'es_field' => 'speaker_name',
                'description' => '發言者（正規化後的姓名，主席已換成主席本人姓名）[例: 林德福]',
                'type' => 'string',
            ],
            '發言者身分' => [
                'es_field' => 'speaker_role',
                'description' => '發言者身分',
                'type' => 'string',
                'enum' => ['委員', '主席', '官員', '其他'],
            ],
            '發言者原文' => [
                'es_field' => 'speaker_raw',
                'description' => '公報上的發言者寫法 [例: 林委員德福]',
                'type' => 'string',
            ],
            '質詢輪次委員' => [
                'es_field' => 'turn_speaker',
                'description' => '這個 block 所在的質詢輪次是哪位委員的（委員質詢與官員答詢同一輪）[例: 林德福]',
                'type' => 'string',
            ],
            '時間來源' => [
                'es_field' => 'time_source',
                'description' => '發言時間的來源：gazette-clock（公報時鐘戳記）／clip（IVOD Clip 起點）／interpolated（沿用前一個錨點）',
                'type' => 'string',
                'enum' => ['gazette-clock', 'clip', 'interpolated'],
            ],
            'IVOD_ID' => [
                'es_field' => 'video.ivod_id',
                'description' => '對應的 Full 影片 IVOD_ID [例: 17504]',
                'type' => 'integer',
            ],
        ];
    }

    public static function getFieldMap()
    {
        return [
            'id' => '發言編號',
            'lcidc_id' => '文件編號',
            'gazette_id' => '公報編號',
            'agenda_id' => '公報議程編號',
            'meet_id' => '會議代碼',
            'meet_type' => '會議種類',
            'term' => '屆',
            'sessionPeriod' => '會期',
            'committees' => '委員會代號',
            'date' => '日期',
            'seq' => '順序',
            'block_line' => '段落行號',
            'kind' => '區塊種類',
            'section' => '段落',
            'speaker_raw' => '發言者原文',
            'speaker_name' => '發言者',
            'speaker_role' => '發言者身分',
            'speaker_title' => '發言者職稱',
            'turn_seq' => '質詢輪次',
            'turn_speaker' => '質詢輪次委員',
            'text' => '內文',
            'chars' => '字數',
            'time_start' => '發言時間',
            'time_source' => '時間來源',
            'clip_id' => 'Clip_IVOD_ID',
            'video' => '影片',
            'video.ivod_id' => 'IVOD_ID',
            'video.offset' => '秒數',
            'page_start' => '公報起始頁',
            'page_end' => '公報結束頁',
            'source_url' => '來源網址',
            'imported_at' => '匯入時間',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '發言編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '1154904_00004-0017',
            ],
        ];
    }

    public static function queryFields()
    {
        return [
            '內文',
            '段落',
        ];
    }

    public static function sortFields()
    {
        return [
            '日期',
            '順序',
            '發言時間',
        ];
    }

    public static function aggMap()
    {
        return [
            '委員會代號' => ['committee', ['委員會代號', '委員會名稱']],
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
                'subject' => '這段發言所屬的會議',
            ],
        ];
    }
}
