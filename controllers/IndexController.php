<?php

class IndexController extends MiniEngine_Controller
{
    public function indexAction()
    {
        $this->view->app_name = getenv('APP_NAME');
    }

    public function robotsAction()
    {
        header('Content-Type: text/plain');
        echo "User-agent: *\nCrawl-delay: 3\n";
        return $this->noview();
    }
}
