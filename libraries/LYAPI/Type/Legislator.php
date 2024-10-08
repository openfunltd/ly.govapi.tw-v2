<?php

class LYAPI_Type_Legislator extends LYAPI_Type
{
    public static function getIdFields()
    {
        return ['屆', '委員姓名'];
    }

    public static function defaultLimit()
    {
        return 200;
    }

    public static function getRelations()
    {
        return [
            'propose_bills' => [
                'type' => 'bill',
                'map' => [
                    '屆' => '屆',
                    '委員姓名' => '提案人',
                ],
            ],
            'cosign_bills' => [
                'type' => 'bill',
                'map' => [
                    '屆' => '屆',
                    '委員姓名' => '連署人',
                ],
            ],
            'meets' => [
                'type' => 'meet',
                'map' => [
                    '屆' => '屆',
                    '委員姓名' => '會議資料.出席立委',
                ],
            ],
            'interpellations' => [
                'type' => 'interpellation',
                'map' => [
                    '屆' => '屆',
                    '委員姓名' => '質詢委員',
                ],
            ],
        ];
    }

    public static function filterFields()
    {
        return [
            '屆' => '',
            '黨籍' => 'party.keyword',
            '選區名稱' => 'areaName.keyword',
            '歷屆立法委員編號' => '',
        ];
    }


    public static function getFieldMap()
    {
        // from https://data.ly.gov.tw/getds.action?id=16
        return [
            'term' => '屆',
            'name' => '委員姓名',
            'ename' => '委員英文姓名',
            'sex' => '性別',
            'party' => '黨籍',
            'partyGroup' => '黨團',
            'areaName' => '選區名稱',
            'committee' => '委員會',
            'onboardDate' => '到職日',
            'degree' => '學歷',
            'experience' => '經歷',
            'picUrl' => '照片位址',
            'leaveFlag' => '是否離職',
            'leaveDate' => '離職日期',
            'leaveReason' => '離職原因',
            'bioId' => '歷屆立法委員編號',
        ];
    }

    public static function customData($data, $id)
    {
        $data->照片位址 = str_replace('http://', 'https://', $data->照片位址);
        return $data;
    }
}
