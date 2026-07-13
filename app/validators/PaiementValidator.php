<?php
/**
 * Validation robuste (côté serveur) de la saisie d'un paiement par le Gérant.
 */
function validate_paiement(array $data): array
{
    $erreurs = [];

    $apprenantId = (int) ($data['apprenant_id'] ?? 0);
    if (!$apprenantId || !apprenant_find($apprenantId)) {
        $erreurs['apprenant_id'] = 'Merci de sélectionner un apprenant valide.';
    }

    $type = $data['type_paiement'] ?? '';
    if (!in_array($type, ['hebdo', 'anniversaire', 'deces'], true)) {
        $erreurs['type_paiement'] = 'Le type de paiement est invalide.';
    }

    $montant = (float) ($data['montant'] ?? 0);
    if ($montant <= 0) {
        $erreurs['montant'] = 'Le montant doit être supérieur à 0.';
    }

    if ($type === 'anniversaire' || $type === 'deces') {
        $campagneId = (int) ($data['campagne_id'] ?? 0);
        $campagne = $campagneId ? campagne_find($campagneId) : null;
        if (!$campagne || $campagne['statut'] !== 'active' || $campagne['type'] !== $type) {
            $erreurs['campagne_id'] = 'Aucune campagne active correspondante n\'a été trouvée.';
        } elseif ($type === 'anniversaire' && $montant !== (float) $campagne['montant_fixe']) {
            $erreurs['montant'] = 'Le montant doit correspondre exactement au montant fixe de la campagne (' . $campagne['montant_fixe'] . ').';
        }
    }

    return $erreurs;
}