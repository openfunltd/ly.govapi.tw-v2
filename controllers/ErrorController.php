<?php

class ErrorController extends MiniEngine_Controller
{
    public function errorAction($error)
    {
        MiniEngine::defaultErrorHandler($error);
    }
}
