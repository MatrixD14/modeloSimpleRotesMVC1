<?php

use app\helpers\Uri;
use app\helpers\Request;

class Router
{
    private static function load(string $controller, string $action)
    {
        try {
            $controllerNamespace = "app\\controllers\\{$controller}";
            if (!class_exists($controllerNamespace)) {
                throw new Exception("Controller file not found: {$controllerNamespace}");
            }
            $controllerInstance = new $controllerNamespace();
            if (!method_exists($controllerInstance, $action)) {
                throw new Exception("Action not found: {$action} in controller {$controllerNamespace}");
            }
            $controllerInstance->$action([
                'get' => $_GET,
                'post' => $_POST
            ]);
        } catch (Exception $e) {
            http_response_code(404);
            echo "Error: " . $e->getMessage();
        }
    }
    private static function routes(): array
    {
        return require  __DIR__ .  '/routers.php';
    }
    public static function execut()
    {
        try {
            $router = self::routes();
            $uri = Uri::get('path');
            $request = Request::get();
            if (!isset($router[$request][$uri]) || !array_key_exists($uri, $router[$request])) {
                throw new Exception('A rota não existe');
            }
            [$controller, $action] = $router[$request][$uri];
            self::load($controller, $action);
        } catch (Exception $e) {
            http_response_code(
                $e->getCode() ?: 500
            );
        }
    }
}
