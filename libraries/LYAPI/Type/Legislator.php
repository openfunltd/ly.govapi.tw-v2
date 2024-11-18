<?php

class LYAPI_Type_Legislator extends LYAPI_Type
{
    public static function getTypeSubject()
    {
        return '立法委員';
    }

    public static function getFilterFieldsInfo(): array
    {
        return [
            '屆' => [
                'es_field' => '',
                'description' => '屆 [例: 11]',
                'type' => 'integer',
            ],
            '黨籍' => [
                'es_field' => 'party.keyword',
                'description' => '黨籍 [例: 民主進步黨]',
                'type' => 'string',
            ],
            '選區名稱' => [
                'es_field' => 'areaName.keyword',
                'description' => '選區名稱 [例: 臺南市第6選舉區]',
                'type' => 'string',
            ],
            '歷屆立法委員編號' => [
                'es_field' => '',
                'description' => '歷屆立法委員編號 [例: 1160]',
                'type' => 'integer',
            ],
        ];
    }

    public static function getIdFieldsInfo()
    {
        return [
            '屆' => [
                'path_name' => 'term',
                'type' => 'integer',
                'example' => 11,
            ],
            '委員姓名' => [
                'path_name' => 'name',
                'type' => 'string',
                'example' => '韓國瑜',
            ],
        ];
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
