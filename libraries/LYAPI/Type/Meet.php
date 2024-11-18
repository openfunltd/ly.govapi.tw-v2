<?php

class LYAPI_Type_Meet extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '會議';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '屆' => [
                'es_field' => '',
                'description' => '屆 [例: 11]',
                'type' => 'integer',
            ],
            '會議代碼' => [
                'es_field' => 'meet_id.keyword',
                'description' => '會議代碼 [例: 院會-11-2-6]',
                'type' => 'string',
            ],
            '會期' => [
                'es_field' => '',
                'description' => '會期 [例: 2]',
                'type' => 'integer',
            ],
            '會議種類' => [
                'es_field' => 'meet_type.keyword',
                'description' => '會議種類 [例: 院會] (TODO: enum)',
                'type' => 'string',
            ],
            '會議資料.出席委員' => [
                'es_field' => 'meet_data.attendLegislator.keyword',
                'description' => '會議資料.出席委員 [例: 陳秀寳]',
                'type' => 'string',
            ],
            '日期' => [
                'es_field' => '',
                'description' => '日期 [例: 2024-10-25]',
                'type' => 'string',
            ],
            '委員會代號' => [
                'es_field' => '',
                'description' => '委員會代號 [例: 23]',
                'type' => 'integer',
            ],
            '會議資料.會議編號' => [
                'es_field' => 'meet_data.meetingNo.keyword',
                'description' => '會議資料.會議編號 [例: 2024102368]',
                'type' => 'string',
            ],
            '議事網資料.關係文書.議案.議案編號' => [
                'es_field' => 'ppg_data.關係文書.bills.billNo.keyword',
                'description' => '議事網資料.關係文書.議案.議案編號 [例: 202110071090000]',
                'type' => 'string',
            ],
        ];
    }

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
            'ppg_data' => '議事網資料', // from https://ppg.ly.gov.tw/ppg/#section-2
            'ppg_data.關係文書.bills' => '議案',
            'ppg_data.關係文書.bills.title' => '標題',
            'ppg_data.關係文書.bills.billNo' => '議案編號',
            'ppg_data.關係文書.bills.comment' => '意見',
            'ppg_data.關係文書.bills.type' => '類別',
            'ppg_data.features' => '資訊',
            'ppg_data.attachments' => '附件',
            'ppg_data.attachments.filetype' => '格式',
            'ppg_data.attachments.href' => '連結',
            'ppg_data.attachments.title' => '標題',
            'ppg_data.attachments.group' => '種類',
            'ppg_data.dates' => '日期',
            'ppg_data.links' => '連結',
            'ppg_data.links.filetype' => '格式',
            'ppg_data.links.href' => '連結',
            'ppg_data.links.title' => '標題',
            'ppg_data.links.type' => '類型',
            'ppg_data.place' => '地點',
            'ppg_data.title' => '標題',
            'ppg_data.content' => '內容',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '會議代碼' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '院會-11-2-3',
            ],
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

    public static function aggMap()
    {
        return [
            '委員會代號' => ['committee', ['委員會代號', '委員會名稱']],
        ];
    }

    public static function getRelations()
    {
        return [
            'ivods' => [
                'type' => 'ivod',
                'map' => [
                    '會議代碼' => '會議.會議代碼',
                ],
            ],
            'bills' => [
                'type' => 'bill',
                'map' => [
                    '會議代碼' => '會議代碼',
                ],
            ],
            'interpellations' => [
                'type' => 'interpellation',
                'map' => [
                    '會議代碼' => '會議代碼',
                ],
            ],
        ];
    }

    public static function customData($data, $id)
    {
        if (is_array($data->{'會議資料'} ?? false) and count($data->{'會議資料'})) {
            foreach ($data->{'會議資料'} as $idx => $meet_data) {
                if (strlen($meet_data->{'會議編號'}) < 15) {
                    $meet_data->ppg_url = sprintf("https://ppg.ly.gov.tw/ppg/sittings/%s/details?meetingDate=%d/%02d/%02d",
                        $meet_data->{'會議編號'},
                        date('Y', strtotime($meet_data->{'日期'})) - 1911,
                        date('m', strtotime($meet_data->{'日期'})),
                        date('d', strtotime($meet_data->{'日期'}))
                    );
                    if ($data->{'議事錄'} ?? false) {
                        $data->{'議事錄'}->ppg_url = $meet_data->ppg_url;
                    }
                }
                $data->{'會議資料'}[$idx] = $meet_data;
            }
        }
        if ($data->{'議事錄'} ?? false) {
            if ($agenda_lcidc_id = ($data->{'議事錄'}->agenda_lcidc_id ?? false)) {
                $data->{'議事錄'}->doc_file = sprintf("https://lydata.ronny-s3.click/agenda-doc/LCIDC01_%s.doc", urlencode($agenda_lcidc_id));
                $data->{'議事錄'}->txt_file = sprintf("https://lydata.ronny-s3.click/agenda-txt/LCIDC01_%s.doc", urlencode($agenda_lcidc_id));
                $data->{'議事錄'}->html_file = sprintf("https://lydata.ronny-s3.click/agenda-html/LCIDC01_%s.doc.html", urlencode($agenda_lcidc_id));
                $data->{'議事錄'}->source_url = sprintf("https://ppg.ly.gov.tw/ppg/publications/official-gazettes/%03d/%02d/%02d/details",
                    $data->{'議事錄'}->comYear,
                    $data->{'議事錄'}->comVolume,
                    $data->{'議事錄'}->comBookId
                );
            } else {
                $data->{'議事錄'}->doc_file = sprintf("https://lydata.ronny-s3.click/meet-proceeding-doc/%s.doc", urlencode($data->{'會議代碼'}));
                $data->{'議事錄'}->txt_file = sprintf("https://lydata.ronny-s3.click/meet-proceeding-txt/%s.txt", urlencode($data->{'會議代碼'}));
                $data->{'議事錄'}->html_file = sprintf("https://lydata.ronny-s3.click/meet-proceeding-html/%s.html", urlencode($data->{'會議代碼'}));
                $data->{'議事錄'}->source_url = $data->{'會議資料'}[0]->ppg_url;
            }
        }

        if ($data->{'公報發言紀錄'} ?? false) {
            foreach ($data->{'公報發言紀錄'} as &$agenda) {
                $agenda->transcript_api = sprintf("https://%s/meet/%s/transcript/%s",
                    $_SERVER['HTTP_HOST'],
                    urlencode($data->{'會議代碼'}),
                    urlencode($agenda->agenda_id ?? '')
                );
                $agenda->html_files = [];
                $agenda->txt_files = [];
                foreach ($agenda->agenda_lcidc_ids ?? [] as $id ){
                    $agenda->html_files[] = sprintf("https://%s/gazette_agenda/%s/html", $_SERVER['HTTP_HOST'], urlencode($id));
                    $agenda->txt_files[] = sprintf("https://lydata.ronny-s3.click/agenda-txt/LCIDC01_%s.doc", urlencode($id));
                }
                // https://ppg.ly.gov.tw/ppg/publications/official-gazettes/106/15/01/details
                $comYear = substr($agenda->gazette_id, 0, 3);
                $comVolume = substr($agenda->gazette_id, 3, strlen($agenda->gazette_id) - 5);
                $comBookId = substr($agenda->gazette_id, -2);
                $agenda->ppg_gazette_url = sprintf("https://ppg.ly.gov.tw/ppg/publications/official-gazettes/%d/%02d/%02d/details",
                    $comYear,
                    $comVolume,
                    $comBookId
                );
            }
        }

        return $data;

    }
}
