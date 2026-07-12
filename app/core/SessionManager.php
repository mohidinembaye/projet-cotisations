<?php

function initialiserUtilisateurs()
{
    if (!isset($_SESSION['users'])) {

        $_SESSION['users'] = [

            [
                'id' => 1,
                'nom' => 'Admin Gérant',
                'email' => 'gerant@cotisations.com',
                'password' => password_hash('gerant123', PASSWORD_DEFAULT),
                'role' => 'gerant'
            ],

            [
                'id' => 2,
                'nom' => 'Coach',
                'email' => 'coach@cotisations.com',
                'password' => password_hash('coach123', PASSWORD_DEFAULT),
                'role' => 'coach'
            ]

        ];
    }
}

function connexion($email, $motDePasse)
{
    foreach ($_SESSION['users'] as $user) {

        if ($user['email'] == $email &&
            password_verify($motDePasse, $user['password'])) {

            $_SESSION['user'] = [
                'id' => $user['id'],
                'nom' => $user['nom'],
                'email' => $user['email'],
                'role' => $user['role']
            ];

            return true;
        }
    }

    return false;
}

function utilisateurConnecte()
{
    return $_SESSION['user'] ?? null;
}

function estConnecte()
{
    return isset($_SESSION['user']);
}

function estGerant()
{
    return estConnecte() && $_SESSION['user']['role'] == 'gerant';
}

function estCoach()
{
    return estConnecte() && $_SESSION['user']['role'] == 'coach';
}

function estApprenant()
{
    return estConnecte() && $_SESSION['user']['role'] == 'apprenant';
}

function deconnexion()
{
    unset($_SESSION['user']);
    session_destroy();
}