<?php

class LYAPI_Type_Legislator extends LYAPI_Type
{
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
