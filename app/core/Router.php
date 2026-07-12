<?php

$routes = [];

function route($method, $url, $controller)
{
    global $routes;

    $routes[] = [
        'method' => strtoupper($method),
        'url' => $url,
        'controller' => $controller
    ];
}

function router_dispatch()
{
    global $routes;

    $method = $_SERVER['REQUEST_METHOD'];
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    foreach ($routes as $route) {

        if ($route['method'] == $method && $route['url'] == $url) {

            if (function_exists($route['controller'])) {
                $route['controller']();
            } else {
                echo "Contrôleur introuvable.";
            }

            return;
        }
    }

    echo "Erreur 404 : Page introuvable.";
}