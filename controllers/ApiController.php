<?php

class ApiController extends MiniEngine_Controller
{
    public function collectionsAction($type)
    {
        OpenFunAPIHelper::checkUsage([
            'service' => 'lyapi2',
            'class' => "{$type}_collection",
        ]);
        $http_status = 200;
        try {
            $ret = LYAPI_SearchAction::getCollections($type, $_SERVER['QUERY_STRING']);
        } catch (Exception $e) {
            $uniqid = MiniEngineHelper::uniqid(8);
            if (strpos($e->getMessage(), 'Result window is too large')) {
                $http_status = 413;
                header('HTTP/1.1 413 Payload Too Large', true, 413);
                $message = "錯誤，請縮小查詢範圍或調整分頁參數後重試";
            } else {
                $http_status = 500;
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
            'count' => $this->countRecords($ret, LYAPI_Type::run($type, 'getReturnKey')),
            'http_status' => $http_status,
        ]);
        return $this->cors_json($ret);
    }

    public function itemAction($type, $id, $sub)
    {
        OpenFunAPIHelper::checkUsage([
            'service' => 'lyapi2',
            'class' => "{$type}_item",
        ]);
        $ret = LYAPI_SearchAction::getItem($type, $id, $sub, $_SERVER['QUERY_STRING']);
        OpenFunAPIHelper::apiDone([
            'size' => strlen(json_encode($ret, JSON_UNESCAPED_UNICODE)),
            'count' => $this->countRecords($ret),
        ]);
        return $this->cors_json($ret);
    }

    /**
     * 計算回應中實際回傳的資料筆數，供 usage log 的 record_count 使用
     * 有指定 $return_key 時直接取該欄位；單筆資料（data）算 1；
     * 子資料（如 /bill/{id}/related_bills）回傳型別不固定，取第一個非中介資料的陣列欄位
     */
    protected function countRecords($ret, $return_key = null)
    {
        if (!is_object($ret) or ($ret->error ?? false)) {
            return 0;
        }
        if ($return_key !== null) {
            return is_array($ret->{$return_key} ?? null) ? count($ret->{$return_key}) : 0;
        }
        if (isset($ret->data)) {
            return 1;
        }
        $meta_keys = ['supported_filter_fields', 'supported_relations', 'relations', 'aggs', 'sort', 'output_fields', 'id_fields'];
        foreach (get_object_vars($ret) as $k => $v) {
            if (is_array($v) and !in_array($k, $meta_keys)) {
                return count($v);
            }
        }
        return 0;
    }
}
