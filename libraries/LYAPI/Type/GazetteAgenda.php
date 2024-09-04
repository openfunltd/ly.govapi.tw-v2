<?php

class LYAPI_Type_GazetteAgenda extends LYAPI_Type
{
    public static function getFieldMap()
    {
        // https://data.ly.gov.tw/getds.action?id=41
        // comYear：卷, comVolume：期, comBookId：冊別, term：屆別, sessionPeriod：會期, sessionTimes：會次, meetingTimes：臨時會會次, agendaNo：目錄編號, agendaType：類別代碼(1:院會、2:國是論壇、3:委員會、4:質詢事項、5:議事錄、8:黨團協商紀錄、9:發言索引、10:報告事項、11:討論事項、12:臨時提案), meetingDate:會議日期(民國年), subject：案由 , PageStart：起始頁碼 , PageEnd：結束頁碼, docUrl：doc檔案下載位置, selectTerm:屆別期別篩選條件
        return [
            'agenda_id' => '公報議程編號',
            'comYear' => '卷',
            'comVolume' => '期',
            'comBookId' => '冊別',
            'term' => '屆',
            'sessionPeriod' => '會期',
            'sessionTimes' => '會次',
            'meetingTimes' => '臨時會會次',
            'agendaNo' => '目錄編號',
            'agendaType' => '類別代碼',
            'meetingDate' => '會議日期',
            'subject' => '案由',
            'pageStart' => '起始頁碼',
            'pageEnd' => '結束頁碼',
            'docUrls' => 'doc檔案下載位置',
            'selectTerm' => '屆別期別篩選條件',
            'gazette_id' => '公報編號',
        ];
    }

    public static function filterFields()
    {
        return [
            '公報編號' => '',
            '卷' => '',
            '屆' => '',
            '會議日期' => '',
        ];
    }

    public static function queryFields()
    {
        return [
            '案由',
        ];
    }

    public static function sortFields()
    {
        return [
            '卷',
            '期',
            '冊別',
        ];
    }
}
