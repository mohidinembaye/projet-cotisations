<?php

function auth_route_login(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        auth_handle_login();
        return;
    }
    auth_show_login();
}

function auth_show_login(array $erreurs = [], array $old = []): void
{
    // Page autonome (écran de connexion) : pas de gabarit sidebar/topbar.
    echo render_partial('auth/connexion', ['erreurs' => $erreurs, 'old' => $old]);
    exit;
}

function auth_handle_login(): void
{
    require_valid_post();

    $email = post('email');
    $password = post('password');
    $erreurs = [];

    if ($email === '') {
        $erreurs['email'] = 'Merci de saisir votre email.';
    }
    if ($password === '') {
        $erreurs['password'] = 'Merci de saisir votre mot de passe.';
    }

    if (!$erreurs) {
        $user = auth_attempt($email, $password);
        if (!$user) {
            $erreurs['email'] = 'Email ou mot de passe incorrect.';
        }
    }

    if ($erreurs) {
        auth_show_login($erreurs, ['email' => $email]);
        return;
    }

    auth_login($user);
    flash_set('success', 'Bienvenue, ' . $user['nom'] . ' !');
    redirect(auth_dashboard_path_for($user['role']));
}

function auth_dashboard_path_for(string $role): string
{
    return match ($role) {
        'gerant' => '/gerant/dashboard',
        'coach' => '/coach/dashboard',
        'apprenant' => '/apprenant/dashboard',
        default => '/login',
    };
}

function auth_route_logout(): void
{
    auth_logout();
    session_manager_start();
    flash_set('success', 'Vous avez été déconnecté.');
    redirect('/login');
}

function auth_route_inscription(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        auth_handle_inscription();
        return;
    }
    auth_show_inscription();
}

function auth_show_inscription(array $erreurs = [], array $old = []): void
{
    // Page autonome (écran d'inscription) : pas de gabarit sidebar/topbar.
    echo render_partial('auth/inscription', ['erreurs' => $erreurs, 'old' => $old]);
    exit;
}

/** Auto-inscription : un apprenant peut créer lui-même son compte. */
function auth_handle_inscription(): void
{
    require_valid_post();

    $data = [
        'prenom' => post('prenom'),
        'nom' => post('nom'),
        'email' => post('email'),
        'password' => post('password'),
        'date_naissance' => post('date_naissance', null) ?: null,
    ];

    if (trim($data['password']) === '' || mb_strlen($data['password']) < 4) {
        auth_show_inscription(['password' => 'Le mot de passe doit contenir au moins 4 caractères.'], $data);
        return;
    }

    [$succes, $resultat] = apprenant_creer($data, 'auto-inscription');

    if (!$succes) {
        auth_show_inscription($resultat, $data);
        return;
    }

    flash_set('success', 'Inscription réussie ! Vous pouvez maintenant vous connecter.');
    redirect('/login');
}