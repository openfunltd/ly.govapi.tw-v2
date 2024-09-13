<?php

class LYAPI_Type_Bill extends LYAPI_Type
{
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

    public static function getIdFields()
    {
        return ['議案編號'];
    }

    public static function filterFields()
    {
        return [
            '屆' => '',
            '會期' => '',
            '議案流程.狀態' => '議案流程.狀態.keyword',
            '議案類別' => '議案類別.keyword',
            '提案人' => '提案人.keyword',
            '連署人' => '連署人.keyword',
            '法律編號' => 'laws.keyword',
            '議案狀態' => '議案狀態.keyword',
            '會議代碼' => 'meet_id.keyword',
            '提案來源' => '提案來源.keyword',
            '議案編號' => 'BillNo.keyword',
            '提案編號' => '提案編號.keyword',
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
        ];
    }

    public static function sortFields()
    {
        return [
            '資料抓取時間',
        ];
    }

    public static function getRelations()
    {
        return [
            'related_bills' => [
                'type' => '_function',
                'function' => 'getRelatedBills',
            ],
        ];
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
        $data->url = sprintf("https://ppg.ly.gov.tw/ppg/bills/%s/details", urlencode($data->議案編號));
        return $data;
    }
}
