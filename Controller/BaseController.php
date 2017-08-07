<?php

namespace Controller;

class BaseController
{
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    protected function getTwig()
    {
        return $this->twig;
    }

    protected function renderView($view, $data = [])
    {
        $template = $this->getTwig()->load($view);
        return $template->render($data);
    }

    protected function redirect($route)
    {
        header('Location: ?action=' . $route);
        exit(0);
    }
}