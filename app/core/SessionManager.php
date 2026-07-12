<?php
/**
 * Toutes les lectures/écritures de $_SESSION passent par ce fichier.
 * Aucune autre partie du code ne touche $_SESSION directement.
 */

function session_manager_start()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_name('cotisations_sid');
    session_start();

    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
}

function session_get($cle, $defaut = null)
{
    return $_SESSION[$cle] ?? $defaut;
}

function session_set($cle, $valeur)
{
    $_SESSION[$cle] = $valeur;
}

function session_has($cle)
{
    return isset($_SESSION[$cle]);
}

/* ------------------------------------------------------------------ */
/* Messages flash — toujours renvoyés sous forme de liste, pour être  */
/* affichés simplement avec un foreach dans les vues.                 */
/* ------------------------------------------------------------------ */

function flash_set($type, $message)
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get()
{
    if (empty($_SESSION['_flash'])) {
        return [];
    }
    $flash = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return [$flash];
}

/* ------------------------------------------------------------------ */
/* Protection CSRF                                                     */
/* ------------------------------------------------------------------ */

function csrf_token()
{
    return $_SESSION['_csrf'] ?? '';
}

function csrf_verify($token)
{
    return is_string($token) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}

/** Vérifie le jeton envoyé en POST ; arrête la requête si invalide. */
function csrf_verifier()
{
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        render_error(403, 'Jeton de sécurité invalide, merci de recharger la page.');
    }
}