<?php

class LogController extends MiniEngine_Controller
{
    public function cronAction()
    {
        if (getenv('ENV') == 'prodction') {
            return $this->notfound();
        }
    }

    public function importAction()
    {
        if (getenv('ENV') == 'prodction') {
            return $this->notfound();
        }
    }
}
