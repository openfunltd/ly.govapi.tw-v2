<?php

class LYAPI_Type_Bill extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '議案';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '屆' => [
                'es_field' => '',
                'description' => '議案所屬屆期 [例: 11]',
                'type' => 'integer',
            ],
            '會期' => [
                'es_field' => '',
                'description' => '議案所屬會期 [例: 2]',
                'type' => 'integer',
            ],
            '議案流程.狀態' => [
                'es_field' => '議案流程.狀態.keyword',
                'description' => '議案流程中曾經有過狀態，字串需完全符合 [例: 排入院會 (交內政委員會)]',
                'type' => 'string',
            ],
            '議案類別' => [
                'es_field' => '議案類別.keyword',
                'description' => '議案類別 [例: 法律案]',
                'type' => 'string',
            ],
            '提案人' => [
                'es_field' => '提案人.keyword',
                'description' => '提案人 [例: 徐欣瑩]',
                'type' => 'string',
            ],
            '連署人' => [
                'es_field' => '連署人.keyword',
                'description' => '連署人 [例: 林德福]',
                'type' => 'string',
            ],
            '法律編號' => [
                'es_field' => 'laws.keyword',
                'description' => '議案相關的法律編號(?) [例: 01254]',
                'type' => 'string',
            ],
            '議案狀態' => [
                'es_field' => '議案狀態.keyword',
                'description' => '議案目前所處狀態 [例: 交付審查]',
                'type' => 'string',
            ],
            '會議代碼' => [
                'es_field' => 'meet_id.keyword',
                'description' => '會議代碼 [例: 院會-11-2-3]',
                'type' => 'string',
            ],
            '提案來源' => [
                'es_field' => '提案來源.keyword',
                'description' => '議案的提案來源屬性 [例: 委員提案] (TODO: enum)',
                'type' => 'string',
            ],
            '議案編號' => [
                'es_field' => 'billNo.keyword',
                'description' => '議案編號 [例: 202110068550000]',
                'type' => 'string',
            ],
            '提案編號' => [
                'es_field' => '提案編號.keyword',
                'description' => '議案的提案編號 [例: 20委11006855]',
                'type' => 'string',
            ],
            '字號' => [
                'es_field' => '字號.keyword',
                'description' => '議案的字號 [例: 院總第20號委員提案第11001661號]',
                'type' => 'string',
            ],
            '法條編號' => [
                'es_field' => '對照表.rows.law_content_id.keyword',
                'description' => '對照表對應的法條編號 [例: 01945:01945:2015-01-22-全文修正:16]',
                'type' => 'string',
            ],
        ];
    }

    public static function getFieldMap()
    {
        return [
            '屆期' => '屆',
            'billNo' => '議案編號',
            'meet_id' => '會議代碼',
            'mtime' => '資料抓取時間',
            'first_time' => '提案日期',
            'last_time' => '最新進度日期',
            'laws' => '法律編號',
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '議案編號' => [
                'path_name' => 'billNo',
                'type' => 'string',
                'example' => '203110077970000',
            ],
        ];
    }

    public static function aggMap()
    {
        return [
            '法律編號' => ['law', ['法律編號', '名稱']],
            '會議代碼' => ['meet', ['會議代碼', '會議標題']],
        ];
    }

    public static function queryFields()
    {
        return [
            '議案名稱',
            '提案單位/提案委員',
            '提案人',
            '連署人',
            '案由',
            '說明',
        ];
    }

    public static function outputFields()
    {
        return [
            '議案編號',
            '相關附件',
            '議案名稱',
            '提案單位/提案委員',
            '議案狀態',
            '資料抓取時間',
            '屆',
            '議案類別',
            '提案來源',
            '會議代碼',
            '會期',
            '字號',
            '提案編號',
            '法律編號',
            '最新進度日期',
            '提案人',
        ];
    }

    public static function sortFields()
    {
        return [
            '最新進度日期',
        ];
    }

    public static function getRelations()
    {
        return [
            'related_bills' => [
                'type' => '_function',
                'function' => 'getRelatedBills',
                'subject' => '相關議案',
            ],
            'doc_html' => [
                'type' => '_function',
                'function' => 'getDocHTML',
                'subject' => '議案文件 HTML 內容',
            ],
            'meets' => [
                'type' => 'meet',
                'map' => [
                    '議案編號' => '議事網資料.關係文書.議案.議案編號',
                ],
                'subject' => '議案相關會議',
            ],
        ];
    }

    public static function getDocHTML($data)
    {
        $billNo = $data->議案編號;
        header('Content-Type: text/html');
        $content = file_get_contents(sprintf("https://lydata.ronny-s3.click/bill-doc-parsed/html/%s.doc.gz", $billNo));
        $content = gzdecode($content);
        if (strpos($content, '{') === 0) {
            $content = json_decode($content);
            $content = $content->content;
            echo base64_decode($content);
        } else {
            echo $content;
        }
        exit;
    }

    public static function getRelatedBills($data)
    {
        $billNo = $data->議案編號;
        $obj = Elastic::dbQuery("/{prefix}bill/_doc/" . urlencode($billNo));
        $source = $obj->_source;
        if ($source->{'議案狀態'} == '三讀') {
            // 如果是三讀的議案，查找相同法條並且同一天三讀通過的法條
            $ret = Elastic::dbQuery("/{prefix}bill/_search", 'GET', json_encode([
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'term' => [
                                    'laws' => $source->laws[0],
                                ],
                            ],
                            [
                                'term' => [
                                    'last_time' => $source->last_time,
                                ],
                            ],
                        ],
                    ],
                ],
            ]));
            $pools = [];
            foreach ($ret->hits->hits as $hit) {
                $pools[$hit->_id] = $hit->_source;
                foreach ($hit->_source->{'關連議案'} as $relbill) {
                    if (!array_key_exists($relbill->billNo, $pools)) {
                        $pools[$relbill->billNo] = true;
                    }
                }
                unset($pools[$hit->_id]->{'關連議案'});
            }

        } else if ($source->{'議案狀態'} == '審查完畢') {
            // 先找找看關聯議案中有沒有三讀通過的
            $pools = [];
            $pools[$billNo] = $source;
            $fetching_bills = [];
            foreach ($source->{'關連議案'} as $relbill) {
                $pools[$relbill->billNo] = true;
                $fetching_bills[] = $relbill->billNo;
            }
            unset($pools[$billNo]->{'關連議案'});
            $ret = Elastic::dbQuery("/{prefix}bill/_search", 'GET', json_encode([
                'query' => [
                    'terms' => [
                        'billNo.keyword' => $fetching_bills,
                    ],
                ],
            ]));
            foreach ($ret->hits->hits as $hit) {
                $pools[$hit->_id] = $hit->_source;
                $source = $hit->_source;
                foreach ($hit->_source->{'關連議案'} as $relbill) {
                    if (!array_key_exists($relbill->billNo, $pools)) {
                        $pools[$relbill->billNo] = true;
                    }
                }
                unset($pools[$hit->_id]->{'關連議案'});
                if ($hit->_source->{'議案狀態'} == '三讀') {
                    $ret = Elastic::dbQuery("/{prefix}bill/_search", 'GET', json_encode([
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'term' => [
                                            'laws' => $source->laws[0],
                                        ],
                                    ],
                                    [
                                        'term' => [
                                            'last_time' => $source->last_time,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]));
                    foreach ($ret->hits->hits as $hit) {
                        $pools[$hit->_id] = $hit->_source;
                        foreach ($hit->_source->{'關連議案'} as $relbill) {
                            if (!array_key_exists($relbill->billNo, $pools)) {
                                $pools[$relbill->billNo] = true;
                            }
                        }
                        unset($pools[$hit->_id]->{'關連議案'});
                    }
                    break;
                }
            }
        } else {
            // 找同一條法律並且提案時間在兩個月內的
            if (!count($source->laws)) {
                throw new Exception('找不到法律代碼，無法查詢');
            }
            $ret = Elastic::dbQuery("/{prefix}bill/_search", 'GET', json_encode([
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'terms' => [
                                    'laws' => $source->laws,
                                ],
                            ],
                            [
                                'range' => [
                                    'first_time' => [
                                        'gte' => date('Y-m-d', strtotime($source->first_time. ' -2 month')),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]));
            $pools = [];
            foreach ($ret->hits->hits as $hit) {
                if ($hit->_id == $billNo) {
                    continue;
                }
                $pools[$hit->_id] = $hit->_source;
                $source = $hit->_source;
                foreach ($hit->_source->{'關連議案'} as $relbill) {
                    if (!array_key_exists($relbill->billNo, $pools)) {
                        $pools[$relbill->billNo] = true;
                    }
                }
                unset($pools[$hit->_id]->{'關連議案'});
                if ($hit->_source->{'議案狀態'} == '三讀') {
                    $ret = Elastic::dbQuery("/{prefix}bill/_search", 'GET', json_encode([
                        'query' => [
                            'bool' => [
                                'must' => [
                                    [
                                        'term' => [
                                            'laws' => $source->laws[0],
                                        ],
                                    ],
                                    [
                                        'term' => [
                                            'last_time' => $source->last_time,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]));
                    foreach ($ret->hits->hits as $hit) {
                        $pools[$hit->_id] = $hit->_source;
                        foreach ($hit->_source->{'關連議案'} as $relbill) {
                            if (!array_key_exists($relbill->billNo, $pools)) {
                                $pools[$relbill->billNo] = true;
                            }
                        }
                        unset($pools[$hit->_id]->{'關連議案'});
                    }
                    break;
                }
            }
        }

        // 把關連議案都補齊
        while (true) {
            $fetching_bills = [];
            foreach ($pools as $billNo => $bill) {
                if ($bill === true) {
                    $fetching_bills[] = $billNo;
                    $pools[$billNo] = $bill;
                }
            }
            if (!count($fetching_bills)) {
                break;
            }

            $ret = Elastic::dbQuery("/{prefix}bill/_search", 'GET', json_encode([
                'query' => [
                    'terms' => [
                        'billNo.keyword' => $fetching_bills,
                    ],
                ],
            ]));
            foreach ($ret->hits->hits as $hit) {
                $pools[$hit->_id] = $hit->_source;
                foreach ($hit->_source->{'關連議案'} as $relbill) {
                    if (!array_key_exists($relbill->billNo, $pools)) {
                        $pools[$relbill->billNo] = true;
                    }
                }
                unset($pools[$hit->_id]->{'關連議案'});
            }
            foreach ($fetching_bills as $billNo) {
                if ($pools[$billNo] === true) {
                    $pools[$billNo] = false;
                }
            }
        }

        $pools = array_filter($pools, function($bill) {
            return $bill !== false;
        });

        return ([
            'error' => false,
            'bills' => array_values($pools),
        ]);
    }

    public static function customData($data, $id)
    {
        foreach ($data->{'相關附件'} ?? [] as $idx => $attachment) {
            if (strpos($attachment->{'名稱'}, '關係文書DOC') !== false) {
                $basename = basename($attachment->{'網址'}, '.doc');
                $data->{'相關附件'}[$idx]->{'HTML結果'} = sprintf("https://%s/bill_doc/%s/html"
                    , $_SERVER['HTTP_HOST']
                    , urlencode($basename)
                );
            }
        }
        $data->url = sprintf("https://ppg.ly.gov.tw/ppg/bills/%s/details", urlencode($data->議案編號));
        return $data;
    }
}
