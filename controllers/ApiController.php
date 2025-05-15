<?php

class ApiController extends MiniEngine_Controller
{
    public function collectionsAction($type)
    {
        OpenFunAPIHelper::checkUsage([
            'project' => 'lyapi2',
            'class' => "{$type}_collection",
        ]);
        $ret = LYAPI_SearchAction::getCollections($type, $_SERVER['QUERY_STRING']);
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
