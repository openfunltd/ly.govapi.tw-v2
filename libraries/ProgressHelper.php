<?php

class ProgressHelper
{
    public static function isRelatedBill($a, $b)
    {
        $match_keys = [];
        foreach ([$a, $b] as $idx => $bill) {
            // check 關聯議案
            $billNos = [$bill->議案編號];
            if ($bill->關連議案) {
                foreach ($bill->關連議案 as $related_bill) {
                    if ($related_bill->billNo ?? false) {
                        $billNos[] = $related_bill->billNo;
                    }
                }
            }
            foreach ($billNos as $key) {
                if (!array_key_exists($key, $match_keys)) {
                    $match_keys[$key] = $idx;
                } elseif ($idx != $match_keys[$key]) {
                    return true;
                }
            }

            // check 是否有同一日委員會審查
            /*
            foreach ($bill->議案流程 as $log) {
                if (in_array($log->狀態, [
                    '委員會審查',
                    '排入院會(討論事項)',
                ])) {
                    $key = "{$log->狀態}-{$log->{'院會/委員會'}}-{$log->日期[0]}";
                    if (!array_key_exists($key, $match_keys)) {
                        $match_keys[$key] = $idx;
                    } elseif ($idx != $match_keys[$key]) {
                        return true;
                    }
                }
            }
             */
        }
        return false;
    }

    public static function dfs($bills, $node, &$visited, &$group)
    {
        $visited[$node] = true;
        $group[] = $bills[$node];
        foreach ($bills as $idx => $bill) {
            if ($node == $idx) {
                continue;
            }
            if (isset($visited[$idx])) {
                continue;
            }
            if (!self::isRelatedBill($bills[$node], $bill)) {
                continue;
            }
            self::dfs($bills, $idx, $visited, $group);
        }

    }

    public static function groupBills($bills)
    {
        $visited = [];
        $groups = [];
        foreach ($bills as $idx => $bill) {
            if (isset($visited[$idx])) {
                continue;
            }
            $group = [];
            self::dfs($bills, $idx, $visited, $group);
            $groups[] = $group;
        }

        $new_groups = [ [] ];
        foreach ($groups as $group) {
            if (count($group) == 1) {
                $new_groups[0][] = $group[0];
            } else {
                $new_groups[] = $group;
            }
        }

        return array_map(['ProgressHelper', 'getLogFromBills'], $new_groups);
    }

    public static function getLogFromBills($bills)
    {
        $ret = new StdClass;
        $ret->id = "未分類";
        $bill_log = [];
        foreach ($bills as $bill) {
            if ($bill->提案來源 == '審查報告') {
            } else {
                $bill_log["提案-{$bill->議案編號}"] = [
                    '關係文書' => [
                        '連結' => $bill->相關附件[0]->網址,
                        'billNo' => $bill->議案編號,
                        '類型' => '提案',
                    ],
                    '主提案' => $bill->提案人[0] ?? $bill->{'提案單位/提案委員'},
                    '進度' => '一讀',
                    '該提案最新狀態' => $bill->議案狀態,
                    '會議日期' => $bill->提案日期,
                    '會議代碼' => $bill->議案流程[0]->會議代碼 ?? '',
                ];
            }

            foreach ($bill->議案流程 as $log) {
                if ($log->狀態 == '委員會發文' and $bill->提案來源 == '審查報告') {
                    $ret->id = "審查完成-{$log->日期[0]}-{$bill->議案編號}";
                    $bill_log["發文-{$bill->議案編號}"] = [
                        '關係文書' => [
                            '連結' => $bill->相關附件[0]->網址,
                            'billNo' => $bill->議案編號,
                            '類型' => '審查報告',
                        ],
                        '進度' => '委員會發文',
                        '會議日期' => $log->日期[0],
                        '會議代碼' => $log->會議代碼 ?? '',
                    ];
                } elseif ($log->狀態 == '委員會審查') {
                    $bill_log["審查-{$log->日期[0]}"] = [
                        '進度' => '委員會審查',
                        '會議日期' => $log->日期[0],
                        '會議代碼' => $log->會議代碼 ?? '',
                    ];
                } elseif ($log->狀態 == '排入院會(討論事項)') {
                    $bill_log["院會-{$log->日期[0]}"] = [
                        '進度' => '二讀(討論)',
                        '會議日期' => $log->日期[0],
                        '會議代碼' => $log->會議代碼 ?? '',
                    ];
                } elseif ($log->狀態 == '三讀') {
                    $ret->id = "三讀-{$log->日期[0]}";
                    $bill_log["三讀-{$log->日期[0]}"] = [
                        '進度' => '三讀',
                        '會議日期' => $log->日期[0],
                        '會議代碼' => $log->會議代碼 ?? '',
                    ];
                }
            }
        }

        $bill_log = array_values($bill_log);
        usort($bill_log, function ($a, $b) {
            // 會議日期優先
            if ($a['會議日期'] != $b['會議日期']) {
                return strtotime($a['會議日期']) - strtotime($b['會議日期']);
            }
            // 一讀最不優先
            if ($a['進度'] == '一讀' and $b['進度'] != '一讀') {
                return -1;
            }
            if ($b['進度'] == '一讀' and $a['進度'] != '一讀') {
                return 1;
            }
            // 包含二讀的一定要比包含三讀的優先
            if (strpos($a['進度'], '二讀') !== false and strpos($b['進度'], '三讀') !== false) {
                return -1;
            }
            if (strpos($b['進度'], '二讀') !== false and strpos($a['進度'], '三讀') !== false) {
                return 1;
            }
            return 0;
        });
        $ret->bill_log = $bill_log;
        return $ret;
    }
}
