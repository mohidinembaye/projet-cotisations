<?php
require_once __DIR__ . '/../models/ApprenantModel.php';


function initialiserComptes()
{
    if (session_has('comptes')) {
        return;
    }

    $comptes = [];
    $comptes[] = [
        'id' => 'gerant_1',
        'role' => 'gerant',
        'prenom' => 'Admin',
        'nom' => 'Gérant',
        'email' => 'gerant@cotisations.test',
        'mot_de_passe' => password_hash('gerant123', PASSWORD_DEFAULT),
    ];
    $comptes[] = [
        'id' => 'coach_1',
        'role' => 'coach',
        'prenom' => 'Coach',
        'nom' => 'Superviseur',
        'email' => 'coach@cotisations.test',
        'mot_de_passe' => password_hash('coach123', PASSWORD_DEFAULT),
    ];

    session_set('comptes', $comptes);
    initialiserApprenants();
}

function trouverCompteParEmail($email)
{
    foreach (session_get('comptes', []) as $compte) {
        if (strcasecmp($compte['email'], $email) === 0) {
            return $compte;
        }
    }
    return null;
}


function authentifier($email, $motDePasse)
{
$email = strtolower(trim($email));
    $compte = trouverCompteParEmail($email);
    if ($compte && password_verify($motDePasse, $compte['mot_de_passe'])) {
        ouvrirSessionUtilisateur([
            'id' => $compte['id'],
            'role' => $compte['role'],
            'nom' => $compte['prenom'] . ' ' . $compte['nom'],
            'email' => $compte['email'],
        ]);
        return true;
    }

    $apprenant = trouverApprenantParEmail($email);
    if ($apprenant && !empty($apprenant['mot_de_passe']) && password_verify($motDePasse, $apprenant['mot_de_passe'])) {
        ouvrirSessionUtilisateur([
            'id' => $apprenant['id'],
            'role' => 'apprenant',
            'nom' => $apprenant['prenom'] . ' ' . $apprenant['nom'],
            'email' => $apprenant['email'],
        ]);
        return true;
    }

    return false;
}

function ouvrirSessionUtilisateur($utilisateur)
{
    session_regenerate_id(true);
    session_set('utilisateur', $utilisateur);
}

function terminerSession()
{
    $_SESSION = [];
    session_destroy();
}

function utilisateurConnecte()
{
    return session_get('utilisateur');
}

function estRole($role)
{
    $utilisateur = utilisateurConnecte();
    return $utilisateur !== null && $utilisateur['role'] === $role;
}


function verifierRole($roles)
{
    $roles = (array) $roles;
    $utilisateur = utilisateurConnecte();

    if ($utilisateur === null) {
        flash_set('error', 'Merci de vous connecter pour accéder à cette page.');
        rediriger('/login');
    }

    if (!in_array($utilisateur['role'], $roles, true)) {
        render_error(403, "Vous n'avez pas les droits nécessaires pour consulter cette page.");
    }
}