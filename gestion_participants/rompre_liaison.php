<?php
session_start();
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');
require_once(__DIR__ . '/../includes/bdd.php');

if (!valider_id('get', 'id', '', 'participations')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_participation = dechiffrer($_GET['id']);

// L'id de la participation est valide. Après ça bh je en pense qu'il y ait d'autres validations à faire

$stmt = $bdd->query('DELETE FROM participations WHERE id=' . $id_participation);

// Bref c'est fini

$_SESSION['liaison_rompue'] = 'La liaison a été rompue avec succès !';
header('location:' . $_SESSION['current_url']);
exit;
