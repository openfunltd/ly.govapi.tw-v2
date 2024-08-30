<?php

class StatController extends MiniEngine_Controller
{
    public function indexAction()
    {
        return $this->json(LYAPI_StatAction::getStat());
    }
}

