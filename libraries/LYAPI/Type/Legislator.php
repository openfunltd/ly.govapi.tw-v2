<?php

class LYAPI_Type_Legislator extends LYAPI_Type
{
    public static function get_id_count()
    {
        return 2;
    }

    public static function defaultLimit()
    {
        return 200;
    }


    public static function getRelations()
    {
        return [
            'propose_bill' => [
                'type' => 'bill',
                'map' => [
                    '屆期' => '屆期',
                    '委員姓名' => '提案人',
                ],
            ],
            'cosign_bill' => [
                'type' => 'bill',
                'map' => [
                    '屆期' => '屆期',
                    '委員姓名' => '連署人',
                ],
            ],
            'meet' => [
                'type' => 'meet',
                'map' => [
                    '屆期' => '屆期',
                    '委員姓名' => '會議資料.出席立委',
                ],
            ],
            'interpellation' => [
                'type' => 'interpellation',
                'map' => [
                    '屆期' => '屆期',
                    '委員姓名' => '質詢委員',
                ],
            ],
        ];
    }

    public static function filterFields()
    {
        return [
            '屆期' => '',
            '黨籍' => 'party.keyword',
            '選區名稱' => 'areaName.keyword',
            '歷屆立法委員編號' => '',
        ];
    }


    public static function getFieldMap()
    {
        // from https://data.ly.gov.tw/getds.action?id=16
        return [
            'term' => '屆期',
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
}
