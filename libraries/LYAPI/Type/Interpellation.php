<?php

class LYAPI_Type_Interpellation extends LYAPI_Type
{
    public static function getFieldMap()
    {
        return [
            'term' => '屆期',
            'legislators' => '質詢委員',
        ];
    }

    public static function filterFields()
    {
        return [
            '質詢委員' => 'legislators.keyword',
            '屆期' => '',
        ];
    }
}
