<?php
session_start();
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');

if (valider_id('get', 'id', '', 'activites')) {
    $idActivite = dechiffrer($_GET['id']);
    $bdd->query('DELETE FROM activites WHERE id=' . $idActivite);
    // Suppression réussie
    $_SESSION['suppression_activite_ok'] = 'L\'activité a été supprimée avec succès';
    header("Location:/gestion_activites/voir_activites.php");
    exit;
} else {
    redirigerVersPageErreur();
}
