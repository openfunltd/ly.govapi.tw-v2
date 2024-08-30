<?php
include(__DIR__ . '/init.inc.php');

MiniEngine::dispatch(function($uri){
    if ($uri == '/robots.txt') {
        return ['index', 'robots'];
    }

    // 處理 /bills, /bill/{id} => bill
    //      /legislators, /legislator/{name} => legislator
    // 依照有沒有 librarys/LYAPI/Type/{Bill,Legislator,Committee}.php 決定
    //   /bills => APIController::collectionAction({"type":"bill"})
    //   /bill/{id} => APIController::itemAction({"type":"bill","id":["id"]})
    //   /bill/{id}/foo => APIController::itemAction({"type":"bill","id":["id"]})
    $param = LYAPI_Helper::getApiType($uri);
    if ($param) {
        return $param;
    }

    // default
    return null;
});
