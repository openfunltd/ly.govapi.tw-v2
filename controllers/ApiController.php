<?php

class ApiController extends MiniEngine_Controller
{
    public function collectionsAction($type)
    {
        OpenFunAPIHelper::checkUsage([
            'project' => 'lyapi2',
            'class' => "{$type}_collection",
        ]);
        try {
            $ret = LYAPI_SearchAction::getCollections($type, $_SERVER['QUERY_STRING']);
        } catch (Exception $e) {
            $uniqid = MiniEngineHelper::uniqid(8);
            if (strpos($e->getMessage(), 'Result window is too large')) {
                header('HTTP/1.1 413 Payload Too Large', true, 413);
                $message = "錯誤，請縮小查詢範圍或調整分頁參數後重試";
            } else {
                header('HTTP/1.1 500 Internal Server Error', true, 500);
                $message = "錯誤，錯誤代碼為 " . $uniqid;
                error_log("[$uniqid] " . $e->getMessage());
            }
            $ret = [
                'error' => true,
                'message' => $message,
            ];
        }
        OpenFunAPIHelper::apiDone([
            'size' => strlen(json_encode($ret, JSON_UNESCAPED_UNICODE)),
        ]);
        return $this->cors_json($ret);
    }

    public function itemAction($type, $id, $sub)
    {
        OpenFunAPIHelper::checkUsage([
            'project' => 'lyapi2',
            'class' => "{$type}_item",
        ]);
        $ret = LYAPI_SearchAction::getItem($type, $id, $sub, $_SERVER['QUERY_STRING']);
        OpenFunAPIHelper::apiDone([
            'size' => strlen(json_encode($ret, JSON_UNESCAPED_UNICODE)),
        ]);
        return $this->cors_json($ret);
    }
}
