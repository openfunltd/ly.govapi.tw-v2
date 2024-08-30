<?php

class LYAPI_StatAction
{
    public static function getStat()
    {
        $records = new StdClass;
        // bill
        $ret = Elastic::dbQuery("/{prefix}bill/_search", 'GET', json_encode([
            'size' => 0,
            'aggs' => [
                'term_count' => [
                    'terms' => [
                        'field' => '屆期',
                        'order' => [ '_key' => 'desc' ],
                    ],
                    'aggs' => [
                        'sessionPeriod_count' => [
                            'terms' => [
                                'field' => '會期',
                                'order' => [ '_key' => 'desc' ],
                            ],
                        ],
                    ],
                ],
                'max_mtime' => [
                    'max' => [
                        'field' => 'mtime',
                    ],
                ],
            ]
        ]));
        $records->bill = new StdClass;
        $records->bill->total = 0;
        $records->bill->terms = [];
        foreach ($ret->aggregations->term_count->buckets as $bucket) {
            $records->bill->total += $bucket->doc_count;
            $records->bill->terms[] = [
                'term' => $bucket->key,
                'count' => $bucket->doc_count,
                'sessionPeriod_count' => [],
            ];
            foreach ($bucket->sessionPeriod_count->buckets as $sessionPeriod_bucket) {
                $records->bill->terms[count($records->bill->terms) - 1]['sessionPeriod_count'][] = [
                    'sessionPeriod' => $sessionPeriod_bucket->key,
                    'count' => $sessionPeriod_bucket->doc_count,
                ];
            }
        }
        $records->bill->max_mtime = $ret->aggregations->max_mtime->value;
        $records->bill->max_mtime_human = date('Y-m-d H:i:s', $records->bill->max_mtime / 1000);

        // legislator
        $ret = Elastic::dbQuery("/{prefix}legislator/_search", 'GET', json_encode([
            'size' => 0,
            'aggs' => [
                'term_count' => [
                    'terms' => [
                        'field' => 'term',
                        'order' => [ '_key' => 'desc' ],
                    ],
                ],
            ]
        ]));
        $records->legislator = new StdClass;
        $records->legislator->total = 0;
        $records->legislator->terms = [];
        foreach ($ret->aggregations->term_count->buckets as $bucket) {
            $records->legislator->total += $bucket->doc_count;
            $records->legislator->terms[] = [
                'term' => $bucket->key,
                'count' => $bucket->doc_count,
            ];
        }

        // gazette
        $ret = Elastic::dbQuery("/{prefix}gazette/_search", 'GET', json_encode([
            'size' => 0,
            'aggs' => [
                'year_count' => [
                    'terms' => [
                        'field' => 'comYear',
                        'order' => [ '_key' => 'desc' ],
                    ],
                ],
            ],
        ]));
        $records->gazette = new StdClass;
        $records->gazette->total = 0;
        $records->gazette->agenda_total = 0;
        $records->gazette->max_meeting_date = 0;
        $records->gazette->max_meeting_date_human = '';
        $records->gazette->comYears = [];
        foreach ($ret->aggregations->year_count->buckets as $bucket) {
            $records->gazette->total += $bucket->doc_count;
            $records->gazette->comYears[$bucket->key] = [
                'year' => $bucket->key,
                'count' => $bucket->doc_count,
            ];
        }

        // gazette_agenda
        $ret = Elastic::dbQuery("/{prefix}gazette_agenda/_search", 'GET', json_encode([
            'size' => 0,
            'aggs' => [
                'year_count' => [
                    'terms' => [
                        'field' => 'comYear',
                        'order' => [ '_key' => 'desc' ],
                    ],
                    'aggs' => [
                        'max_meeting_date' => [
                            'max' => [ 'field' => 'meetingDate' ],
                        ],
                    ],
                ],
            ],
        ]));

        foreach ($ret->aggregations->year_count->buckets as $bucket) {
            $records->gazette->agenda_total += $bucket->doc_count;
            $records->gazette->comYears[$bucket->key]['agenda_count'] = $bucket->doc_count;
            $records->gazette->comYears[$bucket->key]['max_meeting_date'] = $bucket->max_meeting_date->value;
            $records->gazette->comYears[$bucket->key]['max_meeting_date_human'] = date('Y-m-d H:i:s', $bucket->max_meeting_date->value / 1000);
        }
        $records->gazette->comYears = array_values($records->gazette->comYears);
        $records->gazette->max_meeting_date = $ret->aggregations->year_count->buckets[0]->max_meeting_date->value;
        $records->gazette->max_meeting_date_human = date('Y-m-d H:i:s', $records->gazette->max_meeting_date / 1000);

        // meet
        $ret = Elastic::dbQuery("/{prefix}meet/_search", 'GET', json_encode([
            'size' => 0,
            'aggs' => [
                'term_count' => [
                    'terms' => [
                        'field' => 'term',
                        'order' => [ '_key' => 'desc' ],
                    ],
                    'aggs' => [
                        'max_meeting_date' => [
                            'max' => [ 'field' => 'meet_data.date'],
                        ],
                        'term_meetdata_count' => [
                            'filter' => [
                                'exists' => ['field' => 'meet_data.date' ],
                            ],
                        ],
                        'term_議事錄_count' => [
                            'filter' => [
                                'exists' => ['field' => '議事錄' ],
                            ],
                        ],
                        'sessionPeriod_count' => [
                            'terms' => [
                                'field' => 'sessionPeriod',
                                'order' => [ '_key' => 'desc' ],
                            ],
                        ],
                    ],
                ],
            ],
        ]));
        $records->meet = new StdClass;
        $records->meet->total = 0;
        $records->meet->terms = [];
        foreach ($ret->aggregations->term_count->buckets as $bucket) {
            $records->meet->total += $bucket->doc_count;
            $records->meet->terms[$bucket->key] = [
                'term' => $bucket->key,
                'count' => $bucket->doc_count,
            ];
            $records->meet->terms[$bucket->key]['max_meeting_date'] = $bucket->max_meeting_date->value;
            $records->meet->terms[$bucket->key]['max_meeting_date_human'] = date('Y-m-d H:i:s', $bucket->max_meeting_date->value / 1000);
            $records->meet->terms[$bucket->key]['meetdata_count'] = $bucket->term_meetdata_count->doc_count;
            $records->meet->terms[$bucket->key]['議事錄_count'] = $bucket->term_議事錄_count->doc_count;
            $records->meet->terms[$bucket->key]['sessionPeriod_count'] = [];
            foreach ($bucket->sessionPeriod_count->buckets as $sessionPeriod_bucket) {
                $records->meet->terms[$bucket->key]['sessionPeriod_count'][] = [
                    'sessionPeriod' => $sessionPeriod_bucket->key,
                    'count' => $sessionPeriod_bucket->doc_count,
                ];
            }
        }
        $records->meet->terms = array_values($records->meet->terms);

        // ivod
        $ret = Elastic::dbQuery("/{prefix}ivod/_search", 'GET', json_encode([
            'size' => 0,
            'aggs' => [
                'max_meeting_date' => [
                    'max' => [ 'field' => '會議時間' ],
                ],
                'min_meeting_date' => [
                    'min' => [ 'field' => '會議時間' ],
                ],
                'term_count' => [
                    'terms' => [
                        'field' => 'meet.term',
                        'order' => [ '_key' => 'desc' ],
                    ],
                    'aggs' => [
                        'max_meeting_date' => [
                            'max' => [ 'field' => '會議時間' ],
                        ],
                        'min_meeting_date' => [
                            'min' => [ 'field' => '會議時間' ],
                        ],
                        'sessionPeriod_count' => [
                            'terms' => [
                                'field' => 'meet.sessionPeriod',
                                'order' => [ '_key' => 'desc' ],
                            ],
                            'aggs' => [
                                'max_meeting_date' => [
                                    'max' => [ 'field' => '會議時間' ],
                                ],
                                'min_meeting_date' => [
                                    'min' => [ 'field' => '會議時間' ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]));
        $records->ivod = new StdClass;
        $records->ivod->total = 0;
        $records->ivod->max_meeting_date = $ret->aggregations->max_meeting_date->value;
        $records->ivod->max_meeting_date_human = date('Y-m-d H:i:s', $records->ivod->max_meeting_date / 1000);
        $records->ivod->min_meeting_date = $ret->aggregations->min_meeting_date->value;
        $records->ivod->min_meeting_date_human = date('Y-m-d H:i:s', $records->ivod->min_meeting_date / 1000);
        $records->ivod->terms = [];
        foreach ($ret->aggregations->term_count->buckets as $bucket) {
            $records->ivod->total += $bucket->doc_count;
            $records->ivod->terms[$bucket->key] = [
                'term' => $bucket->key,
                'count' => $bucket->doc_count,
            ];
            $records->ivod->terms[$bucket->key]['max_meeting_date'] = $bucket->max_meeting_date->value;
            $records->ivod->terms[$bucket->key]['max_meeting_date_human'] = date('Y-m-d H:i:s', $bucket->max_meeting_date->value / 1000);
            $records->ivod->terms[$bucket->key]['min_meeting_date'] = $bucket->min_meeting_date->value;
            $records->ivod->terms[$bucket->key]['min_meeting_date_human'] = date('Y-m-d H:i:s', $bucket->min_meeting_date->value / 1000);
            $records->ivod->terms[$bucket->key]['sessionPeriod_count'] = [];
            foreach ($bucket->sessionPeriod_count->buckets as $sessionPeriod_bucket) {
                $records->ivod->terms[$bucket->key]['sessionPeriod_count'][] = [
                    'sessionPeriod' => $sessionPeriod_bucket->key,
                    'count' => $sessionPeriod_bucket->doc_count,
                    'max_meeting_date' => $sessionPeriod_bucket->max_meeting_date->value,
                    'max_meeting_date_human' => date('Y-m-d H:i:s', $sessionPeriod_bucket->max_meeting_date->value / 1000),
                    'min_meeting_date' => $sessionPeriod_bucket->min_meeting_date->value,
                    'min_meeting_date_human' => date('Y-m-d H:i:s', $sessionPeriod_bucket->min_meeting_date->value / 1000),
                ];
            }
        }
        $records->ivod->terms = array_values($records->ivod->terms);

        return $records;
    }
}
