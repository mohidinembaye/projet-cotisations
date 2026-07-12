<?php

function rediriger($chemin)
{
    header('Location: ' . $chemin);
    exit;
}

function render_error($code, $message)
{
    http_response_code($code);
    echo '<h1>Erreur ' . $code . '</h1><p>' . htmlspecialchars($message, ENT_QUOTES) . '</p>';
    exit;
}

function e($valeur)
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}