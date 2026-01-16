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
            'first_version' => '最早版本',
            'first_version.date' => '日期',
            'first_version.action' => '動作',
            'first_version.version_id' => '版本編號',
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
            '別名',
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

        $skips = [];
        $query_terms = [];
        $query_terms[] = '法律編號=' . $data['法律編號'];
        $query_terms[] = 'sort=日期>';
        $ret = LYAPI_SearchAction::getCollections('law_version', implode('&', $query_terms));
        foreach ($ret->lawversions as $version) {
            foreach ($version->歷程 ?? [] as $log) {
                foreach ($log->關係文書 ?? [] as $record) {
                    if ($record->billNo ?? false) {
                        $skips[$record->billNo] = true;
                    }
                }
            }
        }


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


        $bills = [];
        foreach ($ret->bills as $bill) {
            if (isset($skips[$bill->議案編號])) {
                continue;
            }
            if ($bill->提案編號 ?? false) {
                if (isset($skips[$bill->提案編號])) {
                    continue;
                }

                $skips[$bill->提案編號] = true;
            }
            $bills[$bill->議案編號] = $bill;
        }

        // 找出有哪些審查會議，透過會議補上議案缺少的審查會議
        $query_terms = [];
        $query_terms[] = '屆=' . $data['屆'];
        $query_terms[] = '議事網資料.關係文書.議案.法律編號=' . $data['法律編號'];
        $query_terms[] = '會議種類=委員會';
        $query_terms[] = '會議種類=聯席會議';

        $ret = LYAPI_SearchAction::getCollections('meet', implode('&', $query_terms));

        foreach ($ret->meets as $meet) {
            $日期 = $meet->日期;
            foreach ($meet->議事網資料 as $meet_record) {
                foreach ($meet_record->關係文書->議案 ?? [] as $record) {
                    $billNo = $record->議案編號 ?? null;
                    if (!$billNo) {
                        continue;
                    }
                    if (!isset($bills[$billNo])) {
                        continue;
                    }
                    // 檢查是否有 審查會議紀錄
                    foreach ($bills[$billNo]->議案流程 ?? [] as $log) {
                        if ($log->狀態 != '委員會審查') {
                            continue;
                        }
                        if ($log->會議代碼 != $meet->會議代碼) {
                            continue;
                        }
                        continue 2;
                    }
                    // 補上審查會議
                    $bill = $bills[$billNo];
                    $log = new stdClass();
                    $log->會期 = sprintf("%02d-%02d-%02d",
                        $meet->屆,
                        $meet->會期,
                        end(explode('-', $meet->會議代碼))
                    );
                    $log->{'院會/委員會'} = '委員會';
                    $log->狀態 = '委員會審查';
                    if ($bill->議案狀態 == '交付審查') {
                        $bill->議案狀態 = '委員會審查';
                    }
                    $log->日期 = $meet->日期;
                    $log->會議代碼 = $meet->會議代碼;
                    $bill->議案流程[] = $log;
                    $bills[$billNo] = $bill;
                }
            }
        }

        $groups = ProgressHelper::groupBills(array_values($bills));
        $data['歷程'] = $groups;
        return $data;
    }
}
