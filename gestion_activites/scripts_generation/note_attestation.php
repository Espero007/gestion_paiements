<?php
// Inclusions
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Vérifier que $bdd est un objet PDO
// if (!($bdd instanceof PDO)) {
//     ob_end_clean();
//     die('Erreur : la connexion à la base de données a échouée.');
// }

$redirect = true;

if (valider_id('get', 'id', '', 'participations_activites')) {
    // l'id de l'activité est bon
    if (isset($_GET['document'])) {
        if (in_array($_GET['document'], ['attestation', 'note'])) {
            // On a la variable 'document' et elle a une bonne valeur
            $redirect = false;
            $activity_id = dechiffrer($_GET['id']);
            $document = $_GET['document'];
        }
    }
}

if ($redirect) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

genererNoteAttestation($activity_id, $document);
