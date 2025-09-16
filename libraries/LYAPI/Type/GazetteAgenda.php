<?php

class LYAPI_Type_GazetteAgenda extends LYAPI_Type
{
    public static function getEnpointGroup()
    {
        return 'Gazette';
    }

    public static function getTypeSubject()
    {
        return '公報目錄';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '公報編號' => [
                'es_field' => '',
                'description' => '公報編號 [例: 1137701]',
                'type' => 'string',
            ],
            '卷' => [
                'es_field' => '',
                'description' => '卷 [例: 113]',
                'type' => 'integer',
            ],
            '期' => [
                'es_field' => '',
                'description' => '期 [例: 77]',
                'type' => 'integer',
            ],
            '冊別' => [
                'es_field' => '',
                'description' => '冊別 [例: 1]',
                'type' => 'integer',
            ],
            '屆' => [
                'es_field' => '',
                'description' => '屆 [例: 11]',
                'type' => 'integer',
            ],
            '會議日期' => [
                'es_field' => '',
                'description' => '會議日期 [例: 2024-10-04]',
                'type' => 'string',
            ],
        ];
    }

    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/getds.action?id=41
        // comYear：卷, comVolume：期, comBookId：冊別, term：屆別, sessionPeriod：會期, sessionTimes：會次, meetingTimes：臨時會會次, agendaNo：目錄編號, agendaType：類別代碼(1:院會、2:國是論壇、3:委員會、4:質詢事項、5:議事錄、8:黨團協商紀錄、9:發言索引、10:報告事項、11:討論事項、12:臨時提案), meetingDate:會議日期(民國年), subject：案由 , PageStart：起始頁碼 , PageEnd：結束頁碼, docUrl：doc檔案下載位置, selectTerm:屆別期別篩選條件
        return [
            'agenda_id' => '公報議程編號',
            'comYear' => '卷',
            'comVolume' => '期',
            'comBookId' => '冊別',
            'term' => '屆',
            'sessionPeriod' => '會期',
            'sessionTimes' => '會次',
            'meetingTimes' => '臨時會會次',
            'agendaNo' => '目錄編號',
            'agendaType' => '類別代碼',
            'meetingDate' => '會議日期',
            'subject' => '案由',
            'pageStart' => '起始頁碼',
            'pageEnd' => '結束頁碼',
            'docUrls' => 'doc檔案下載位置',
            'selectTerm' => '屆別期別篩選條件',
            'gazette_id' => '公報編號',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '公報議程編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '1137701_00001',
            ],
        ];
    }

    public static function queryFields()
    {
        return [
            '案由',
        ];
    }

    public static function sortFields()
    {
        return [
            '卷',
            '期',
            '冊別',
        ];
    }

    public static function customData($data, $id)
    {
        $data->{'公報網網址'} = sprintf("https://ppg.ly.gov.tw/ppg/publications/official-gazettes/%03d/%02d/%02d/details",
            $data->{'卷'},
            $data->{'期'},
            $data->{'冊別'}
        );
        $data->{'公報完整PDF網址'} = sprintf("https://ppg.ly.gov.tw/ppg/PublicationBulletinDetail/download/communique1/final/pdf/%d/%02d/LCIDC01_%03d%02d%02d.pdf",
            $data->{'卷'},
            $data->{'期'},
            $data->{'卷'},
            $data->{'期'},
            $data->{'冊別'}
        );
        $data->{'處理後公報網址'} = [];
        foreach ($data->{'doc檔案下載位置'} as $idx => $doc_url) {
            if (!preg_match('#LCIDC01_([0-9_]+)#', $doc_url, $matches)) {
                continue;
            }
            foreach (['html', 'tikahtml', 'txt', 'parsed'] as $type) {
                $data->{'處理後公報網址'}[] = [
                    'type' => $type,
                    'no' => $idx,
                    'url' => sprintf("https://%s/gazette_agenda_doc/%s/%s", $_SERVER['HTTP_HOST'], $matches[1], $type),
                ];
            }
        }

        return $data;
    }
}
