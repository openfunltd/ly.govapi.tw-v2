<?php

class LYAPI_Type_Law extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '法律';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '法律編號' => [
                'es_field' => 'id.keyword',
                'description' => '法律編號 [例: 09200015]',
                'type' => 'string',
            ],
            '類別' => [
                'es_field' => 'type.keyword',
                'description' => '類別',
                'type' => 'string',
                'enum' => ['母法', '子法'],
            ],
            '母法編號' => [
                'es_field' => 'parent.keyword',
                'description' => '母法編號 [例: 09200]',
                'type' => 'string',
            ],
            '法律狀態' => [
                'es_field' => 'status.keyword',
                'description' => '法律狀態 [例: 現行]',
                'type' => 'string',
            ],
            '主管機關' => [
                'es_field' => 'categories.keyword',
                'description' => '主管機關 [例: 總統府]',
                'type' => 'string',
            ],
            '最新版本.日期' => [
                'es_field' => 'latest_version.date',
                'description' => '最新版本日期 [例: 2024-10-25]',
                'type' => 'string',
            ],
        ];
    }

    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/
        //  => https://data.ly.gov.tw/odw/LawNo.pdf 法律編號
        return [
            'id' => '法律編號',
            'type' => '類別',
            'parent' => '母法編號',
            'name' => '名稱',
            'name_other' => '其他名稱',
            'name_aka' => '別名',
            'categories' => '主管機關',
            'status' => '法律狀態',
            'latest_version' => '最新版本',
            'latest_version.date' => '日期',
            'latest_version.action' => '動作', 
            'latest_version.version_id' => '版本編號',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '法律編號' => [
                'path_name' => 'id',
                'type' => 'string',
                'example' => '09200015',
            ],
        ];
    }

    public static function queryFields()
    {
        return [
            '名稱',
            '其他名稱',
        ];
    }

    public static function getRelations()
    {
        return [
            'progress' => [
                'type' => '_function',
                'function' => 'getProgress',
                'subject' => '未議決進度',
            ],
            'bills' => [
                'type' => 'bill',
                'map' => [
                    '法律編號' => '法律編號',
                ],
                'subject' => '法律相關議案',
            ],
            'versions' => [
                'type' => 'law_version',
                'map' => [
                    '法律編號' => '法律編號',
                ],
                'subject' => '法律過往的版本紀錄',
            ],
        ];
    }

    public static function sortFields()
    {
        return [
            '最新版本.日期',
            '法律編號',
        ];
    }

    public static function getProgress($law)
    {
        $data = [];
        $data['法律編號'] = $law->法律編號;
        $data['法律編號:str'] = $law->名稱;
        $data['屆'] = $_GET['屆'] ?? 11;
        $data['歷程'] = [];

        $query_terms = [];
        $query_terms[] = '屆=' . $data['屆'];
        $query_terms[] = '法律編號=' . $data['法律編號'];
        $query_terms[] = 'limit=1000';
        $query_terms[] = 'output_fields=相關附件';
        $query_terms[] = 'output_fields=提案人';
        $query_terms[] = 'output_fields=議案狀態';
        $query_terms[] = 'output_fields=議案流程';
        $query_terms[] = 'output_fields=關連議案';
        $query_terms[] = 'output_fields=議案編號';
        $query_terms[] = 'output_fields=提案日期';
        $query_terms[] = 'output_fields=提案來源';
        $query_terms[] = 'output_fields=提案編號';
        $query_terms[] = 'output_fields=提案單位/提案委員';

        $ret = LYAPI_SearchAction::getCollections('bill', implode('&', $query_terms));
        // 先找出所有三讀的關聯議案
        $skips = [];
        foreach ($ret->bills as $bill) {
            if ($bill->議案狀態 == '三讀') {
                $skips[$bill->議案編號] = true;
                foreach ($bill->關連議案 as $related) {
                    $skips[$related->billNo] = true;
                }
                continue;
            }
        }
        $bill_log = [];
        foreach ($ret->bills as $bill) {
            if (isset($skips[$bill->議案編號])) {
                continue;
            }
            if (isset($skips[$bill->提案編號])) {
                continue;
            }
            $skips[$bill->提案編號] = true;

            if ($bill->提案來源 == '審查報告') {
                $bill_log["發文-{$bill->議案編號}"] = [
                    '關係文書' => [
                        '連結' => $bill->相關附件[0]->網址,
                        'billNo' => $bill->議案編號,
                        '類型' => '審查報告',
                    ],
                    '進度' => '委員會發文',
                    '會議日期' => $log->日期[0],
                ];
            } else {
                $bill_log["提案-{$bill->議案編號}"] = [
                    '關係文書' => [
                        '連結' => $bill->相關附件[0]->網址,
                        'billNo' => $bill->議案編號,
                        '類型' => '提案',
                    ],
                    '主提案' => $bill->提案人[0] ?? $bill->{'提案單位/提案委員'},
                    '進度' => '一讀',
                    '會議日期' => $bill->提案日期,
                ];
            }

            foreach ($bill->議案流程 as $log) {
                if ($log->狀態 == '委員會發文' and $bill->提案來源 != '委員提案') {
                } elseif ($log->狀態 == '委員會審查') {
                    $bill_log["審查-{$log->日期[0]}"] = [
                        '進度' => '委員會審查',
                        '會議日期' => $log->日期[0],
                    ];
                } elseif ($log->狀態 == '排入院會(討論事項)') {
                    $bill_log["院會-{$log->日期[0]}"] = [
                        '進度' => '二讀',
                        '會議日期' => $log->日期[0],
                    ];
                }
            }
        }
        $bill_log = array_values($bill_log);
        usort($bill_log, function ($a, $b) {
            return strtotime($a['會議日期']) - strtotime($b['會議日期']);
        });
        $data['歷程'] = $bill_log;

        return [
            'error' => false,
            'data' => $data,
        ];
    }
}
