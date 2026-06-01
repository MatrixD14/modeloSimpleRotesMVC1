<?php

namespace app\controllers;

use app\helpers\View;

abstract class Controller
{

    protected function view(string $view, array $data = [])
    {
        $viewPath = dirname(__FILE__, 2) . DIRECTORY_SEPARATOR . 'views';
        if (!file_exists($viewPath . DIRECTORY_SEPARATOR . $view . '.php')) {
            throw new \Exception('A view {$view} nao exist');
        }
        View::render($view, $data);
    }
}
