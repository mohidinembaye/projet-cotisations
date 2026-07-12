<?php
/**
 * Validation robuste (côté serveur) des données de formulaire "Campagne",
 * avec des règles spécifiques selon le type d'événement.
 */
function validate_campagne(array $data): array
{
    $erreurs = [];
    $type = $data['type'] ?? '';

    if (!in_array($type, CAMPAGNE_TYPES, true)) {
        $erreurs['type'] = 'Le type de campagne est invalide.';
        return $erreurs;
    }

    if (trim($data['nom'] ?? '') === '') {
        $erreurs['nom'] = 'Le nom de la campagne est obligatoire.';
    }

    if ($type === 'anniversaire') {
        $mois = date('Y-m');
        if (campagne_anniversaire_du_mois_existe($mois)) {
            $erreurs['type'] = 'Une campagne Anniversaire est déjà active ce mois-ci.';
        }
        if (!isset($data['montant_fixe']) || (float) $data['montant_fixe'] <= 0) {
            $erreurs['montant_fixe'] = 'Le montant par personne doit être supérieur à 0.';
        }
        if (!semaine_dernieres_du_mois_courant()) {
            $erreurs['type'] = "La collecte Anniversaire ne peut être programmée que sur la dernière semaine du mois en cours.";
        }
    } elseif ($type === 'autre') {
        if (!isset($data['montant_fixe']) || (float) $data['montant_fixe'] <= 0) {
            $erreurs['montant_fixe'] = 'Le montant par personne doit être supérieur à 0.';
        }
        $dateLimite = $data['date_limite'] ?? '';
        if ($dateLimite === '' || strtotime($dateLimite) === false) {
            $erreurs['date_limite'] = 'La date limite est obligatoire.';
        } elseif (strtotime($dateLimite) < strtotime('today')) {
            $erreurs['date_limite'] = 'La date limite doit être dans le futur.';
        }
    }
    // "deces" : aucun montant à valider (libre), délai fixé automatiquement à 7 jours.

    return $erreurs;
}