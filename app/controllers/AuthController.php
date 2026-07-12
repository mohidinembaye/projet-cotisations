<?php
require_once __DIR__ . '/../core/Auth.php';

function connexion()
{
    $erreurs = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verifier();
        $email = trim($_POST['email'] ?? '');
        $motDePasse = $_POST['mot_de_passe'] ?? '';

        if (authentifier($email, $motDePasse)) {
            $utilisateur = utilisateurConnecte();
            if ($utilisateur['role'] === 'gerant') {
                rediriger('/gerant/dashboard');
            } elseif ($utilisateur['role'] === 'apprenant') {
                rediriger('/apprenant/dashboard');
            } else {
                rediriger('/coach/dashboard');
            }
        }

        $erreurs[] = 'Identifiants incorrects.';
    }

    $flash = flash_get();
    require __DIR__ . '/../views/auth/connexion.php';
}

function deconnexion()
{
    terminerSession();
    session_manager_start();
    flash_set('success', 'Vous êtes déconnecté.');
    rediriger('/login');
}