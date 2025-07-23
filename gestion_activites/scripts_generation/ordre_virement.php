<?php
// Inclusions
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Validations pour les informations à récupérer par GET
$redirect = true;
if (valider_id('get', 'id', $bdd, 'participations_activites')) {
    // Il faut maintenant s'assurer que la banque reçue est valable
    $id_activite = dechiffrer($_GET['id']);
    if (isset($_GET['banque']) && in_array($_GET['banque'], listeBanques($id_activite))) {
        $banque = $_GET['banque'];
        $redirect = false;
    }
}

if ($redirect) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// $zip = isset($_GET['zip']) ? 1 : 0;

genererOrdreVirement($id_activite, $banque);
    
// if($zip){
//     $pdf->Output($dossier_zip.'/Ordre de virement '.$banque.'.pdf', 'F'); 
// }