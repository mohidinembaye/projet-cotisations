<?php
/**
 * Routeur simple : chaque route associe une méthode HTTP et un chemin
 * exact à une fonction de contrôleur.
 */

$GLOBALS['routes'] = [];

function route($methode, $chemin, $gestionnaire)
{
    $GLOBALS['routes'][] = [strtoupper($methode), $chemin, $gestionnaire];
}

function router_dispatch()
{
    $methode = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $uri = rtrim($uri, '/');
    if ($uri === '') {
        $uri = '/';
    }

    foreach ($GLOBALS['routes'] as [$routeMethode, $routeChemin, $gestionnaire]) {
        if ($routeMethode === $methode && $routeChemin === $uri) {
            if (!function_exists($gestionnaire)) {
                render_error(500, "Contrôleur introuvable : $gestionnaire");
            }
            $gestionnaire();
            return;
        }
    }

    render_error(404, "La page demandée n'existe pas.");
}