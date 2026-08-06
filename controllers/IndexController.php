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

    public function skillAction()
    {
        header('Content-Type: text/markdown; charset=utf-8');
        echo file_get_contents(MINI_ENGINE_ROOT . '/skill.md');
        return $this->noview();
    }

    public function knowledgeAction()
    {
        header('Content-Type: text/markdown; charset=utf-8');
        echo file_get_contents(MINI_ENGINE_ROOT . '/knowledge.md');
        return $this->noview();
    }
}
