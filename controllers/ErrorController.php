<?php

class ErrorController extends MiniEngine_Controller
{
    public function errorAction($error)
    {
        echo "Error: " . $error->getMessage();
        return $this->noview();
    }
}
