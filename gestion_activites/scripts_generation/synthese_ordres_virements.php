<?php
// Inclusions
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Validations 
if (!valider_id('get', 'id', $bdd, 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// Il me faut la liste des banques et les montants associés à chacune de ces banques
$id_activite = $_GET['id'];

genererSyntheseOrdres($id_activite);