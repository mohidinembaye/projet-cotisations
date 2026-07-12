<?php
/**
 * Validation robuste (côté serveur) des données de formulaire "Apprenant".
 * Retourne un tableau d'erreurs (vide si les données sont valides).
 */
function validate_apprenant(array $data, array $apprenantsExistants = []): array
{
    $erreurs = [];

    if (trim($data['prenom'] ?? '') === '') {
        $erreurs['prenom'] = 'Le prénom est obligatoire.';
    } elseif (mb_strlen($data['prenom']) > 60) {
        $erreurs['prenom'] = 'Le prénom est trop long (60 caractères max).';
    }

    if (trim($data['nom'] ?? '') === '') {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($data['nom']) > 60) {
        $erreurs['nom'] = 'Le nom est trop long (60 caractères max).';
    }

    $email = trim($data['email'] ?? '');
    if ($email === '') {
        $erreurs['email'] = "L'email est obligatoire.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "Le format de l'email est invalide.";
    } else {
        foreach ($apprenantsExistants as $a) {
            if (strcasecmp($a['email'], $email) === 0) {
                $erreurs['email'] = 'Cet email est déjà utilisé par un autre apprenant.';
                break;
            }
        }
    }

    return $erreurs;
}