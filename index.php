<?php
include(__DIR__ . '/init.inc.php');

if (strpos($_SERVER['REQUEST_URI'], '/v2') === 0) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 3);
}

MiniEngine::dispatch(function($uri){
    if (strpos($uri, '/v2') === 0) {
        $uri = substr($uri, 3);
    }
    if ($uri == '/robots.txt') {
        return ['index', 'robots'];
    }

    if ($uri == '/swagger') {
        return ['swagger', 'ui'];
    }

    if ($uri == '/swagger.yaml') {
        return ['swagger', 'index'];
    }

    if (preg_match('#^/gazette_agenda_doc/([0-9_]+)/([^/]+)$#', $uri, $matches)) {
        return ['gazette_agenda_doc', 'show', [$matches[1], $matches[2]]];
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
