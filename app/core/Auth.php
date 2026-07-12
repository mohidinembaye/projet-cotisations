<?php
/**
 * Authentification & Autorisation (procédural).
 *
 * Comptes disponibles :
 *  - "gerant" et "coach" : comptes uniques, initialisés (seed) au démarrage.
 *  - "apprenant" : comptes créés via auto-inscription, ajout manuel ou import
 *    par le Gérant (stockés dans la collection "apprenants").
 *
 * Les mots de passe sont hashés (password_hash) même en session, par bonne
 * pratique de sécurité.
 */

const ROLES = ['gerant', 'coach', 'apprenant'];

/**
 * Initialise les comptes de démonstration une seule fois par "vie" de
 * l'application (persistant en session tant qu'elle n'est pas détruite).
 */
function auth_seed_users(): void
{
    if (session_has('users')) {
        return;
    }

    $users = [];
    $users[] = [
        'id' => 1,
        'role' => 'gerant',
        'nom' => 'Admin Gérant',
        'email' => 'gerant@cotisations.app',
        'password_hash' => password_hash('gerant123', PASSWORD_DEFAULT),
    ];
    $users[] = [
        'id' => 2,
        'role' => 'coach',
        'nom' => 'Coach Superviseur',
        'email' => 'coach@cotisations.app',
        'password_hash' => password_hash('coach123', PASSWORD_DEFAULT),
    ];

    session_set('users', $users);
    session_set('_users_next_id', 3);
}

/** Retourne tous les comptes non-apprenants (gérant, coach). */
function auth_users(): array
{
    return session_get('users', []);
}

/** Trouve un utilisateur (gérant/coach) par email. */
function auth_find_user_by_email(string $email): ?array
{
    foreach (auth_users() as $user) {
        if (strcasecmp($user['email'], $email) === 0) {
            return $user;
        }
    }
    return null;
}

/**
 * Tente une connexion en cherchant d'abord parmi les comptes gérant/coach,
 * puis parmi les apprenants. Retourne l'utilisateur authentifié (sans le
 * hash) ou null si échec.
 */
function auth_attempt(string $email, string $password): ?array
{
    $email = mb_strtolower(trim($email));

    $user = auth_find_user_by_email($email);
    if ($user && password_verify($password, $user['password_hash'])) {
        return ['id' => $user['id'], 'role' => $user['role'], 'nom' => $user['nom'], 'email' => $user['email']];
    }

    $apprenant = apprenant_find_by_email($email);
    if ($apprenant && !empty($apprenant['password_hash']) && password_verify($password, $apprenant['password_hash'])) {
        return [
            'id' => $apprenant['id'],
            'role' => 'apprenant',
            'nom' => $apprenant['prenom'] . ' ' . $apprenant['nom'],
            'email' => $apprenant['email'],
        ];
    }

    return null;
}

function auth_login(array $user): void
{
    session_set('auth_user', $user);
    session_regenerate_id(true);
    session_set('auth_user', $user); // ré-écrit après régénération de l'id
}

function auth_logout(): void
{
    session_set('auth_user', null);
    session_destroy();
}

function auth_current_user(): ?array
{
    return session_get('auth_user');
}

function auth_is_role(string $role): bool
{
    $user = auth_current_user();
    return $user !== null && $user['role'] === $role;
}

/** Bloque l'accès si l'utilisateur n'a pas l'un des rôles autorisés. */
function auth_require_role(array $roles): void
{
    $user = auth_current_user();
    if ($user === null) {
        flash_set('error', 'Merci de vous connecter pour accéder à cette page.');
        redirect('/login');
    }
    if (!in_array($user['role'], $roles, true)) {
        render_error(403, "Vous n'avez pas les droits nécessaires pour consulter cette page.");
    }
}