<?php

/**
 * 會議段落（meet_section index）：公報逐字稿在「一次發言」（/speeches）之上再疊一層「單元 → 段」的標註，
 * 給網站畫段落目錄。切分規則來自 James 的會議紀錄切分研究（院會、委員會預算審查、公聽會、黨團協商），
 * 匯入端：ly.govapi.tw 的 imports/segment/（segment_meet.py）。
 * 一段一筆，`發言起`～`發言迄` 是這段涵蓋的 /speeches 順序（同一份公報文件內），用 `/speeches?文件編號=…&順序:起,迄` 取內文。
 */
class LYAPI_Type_MeetSection extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '會議段落';
    }

    public static function getReturnKey()
    {
        return 'meet_sections';
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
                'es_field' => 'meet_id',
                'description' => '會議代碼 [例: 委員會-11-5-20-18]',
                'type' => 'string',
            ],
            '會議種類' => [
                'es_field' => 'meet_type',
                'description' => '會議種類 [例: 委員會]',
                'type' => 'string',
            ],
            '切分器' => [
                'es_field' => 'segmenter',
                'description' => '用哪一類切分規則：院會／委員會預算審查／公聽會／黨團協商',
                'type' => 'string',
                'enum' => ['院會', '委員會預算審查', '公聽會', '黨團協商'],
            ],
            '日期' => [
                'es_field' => 'date',
                'description' => '這段所在公報文件的會議日期 [例: 2026-06-10]',
                'type' => 'string',
            ],
            '文件編號' => [
                'es_field' => 'lcidc_id',
                'description' => '公報子文件編號，與 /speeches 的文件編號相同 [例: 1154904_00004]',
                'type' => 'string',
            ],
            '單元序' => [
                'es_field' => 'session_no',
                'description' => '單元在這場會議的順序（從 1 起）',
                'type' => 'integer',
            ],
            '單元種類' => [
                'es_field' => 'session_kind',
                'description' => '切分器給的單元種類 [例: 討論事項、qa、專家發言]',
                'type' => 'string',
            ],
            '段種類' => [
                'es_field' => 'unit_kind',
                'description' => '段的種類：person（人段）／case（案段）／stage（階段段）／group（分組標題）／procedure（過場、程序段）／attachment（書面資料）',
                'type' => 'string',
                'enum' => ['person', 'case', 'stage', 'group', 'procedure', 'attachment'],
            ],
            '標籤' => [
                'es_field' => 'tags',
                'description' => '段上的標籤 [例: 表決、三讀、逐條討論、協商結論]',
                'type' => 'string',
            ],
            '發言者' => [
                'es_field' => 'speakers',
                'description' => '這段裡出現的發言者（公報寫法）[例: 林委員德福]',
                'type' => 'string',
            ],
        ];
    }

    public static function getFieldMap()
    {
        return [
            'id' => '段落編號',
            'meet_id' => '會議代碼',
            'meet_type' => '會議種類',
            'segmenter' => '切分器',
            'segmenter_version' => '切分版本',
            'term' => '屆',
            'date' => '日期',
            'session_no' => '單元序',
            'session_name' => '單元名稱',
            'session_kind' => '單元種類',
            'session_dates' => '單元日期',
            'session_tags' => '單元標籤',
            'section_no' => '段序',
            'section_type' => '段類型',
            'name' => '段名',
            'tags' => '標籤',
            'unit_kind' => '段種類',
            'vote' => '表決',
            'vote.item' => '案號',
            'vote.what' => '事項',
            'vote.type' => '型態',
            'lcidc_id' => '文件編號',
            'seq_start' => '發言起',
            'seq_end' => '發言迄',
            'seqs' => '發言順序',
            'block_count' => '發言數',
            'speakers' => '發言者',
            'sample' => '摘句',
            'updated_at' => '更新時間',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '段落編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '委員會-11-5-20-18|3|4',
            ],
        ];
    }

    public static function queryFields()
    {
        return [
            '段名',
            '單元名稱',
            '摘句',
        ];
    }

    public static function sortFields()
    {
        return [
            '日期',
            '單元序',
            '段序',
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
                'subject' => '這段所屬的會議',
            ],
            'speeches' => [
                'type' => 'speech',
                'map' => [
                    '文件編號' => '文件編號',
                ],
                'subject' => '這段所在公報文件的全部發言（再用 順序:發言起,發言迄 篩出這一段）',
            ],
        ];
    }
}
