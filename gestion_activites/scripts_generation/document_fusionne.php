<?php
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

use setasign\Fpdi\Tcpdf\Fpdi;

/** Générations des fichiers à inclure dans le document fusionné */

if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_activite = dechiffrer($_GET['id']);

genererDocumentsFusionnes($id_activite);
