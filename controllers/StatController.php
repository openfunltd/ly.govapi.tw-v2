<?php

class StatController extends MiniEngine_Controller
{
    public function indexAction()
    {
        return $this->cors_json(LYAPI_StatAction::getStat());
    }
}

