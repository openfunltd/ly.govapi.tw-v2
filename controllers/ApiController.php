<?php

class ApiController extends MiniEngine_Controller
{
    public function collectionsAction($type)
    {
        return $this->cors_json(LYAPI_SearchAction::getCollections($type, $_SERVER['QUERY_STRING']));
    }

    public function itemAction($type, $id, $sub)
    {
        return $this->cors_json(LYAPI_SearchAction::getItem($type, $id, $sub, $_SERVER['QUERY_STRING']));
    }
}
