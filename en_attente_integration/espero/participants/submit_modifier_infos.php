<?php
session_start();
require_once(__DIR__ . '/../../../includes/bdd.php');
require_once(__DIR__ . '/../../../includes/constantes_utilitaires.php');
// Récupération du protocole (http ou https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
// Récupération du nom de domaine + port si nécessaire
$host = $_SERVER['HTTP_HOST'];
// Récupération du chemin URI
$request_uri = $_SERVER['REQUEST_URI'];
// URL complète
$current_url = $protocol . $host . $request_uri;

// Validation id

if(!isset($_GET['id_participant'])){
    redirigerVersPageErreur(404, $current_url);
}else{
    if(valider_id_participant($_GET['id_participant'], $bdd, $current_url)){
        // L'id est valide. Je vais prendre les informations du participant
        $id_participant = $_GET['id_participant'];

        $stmt = "SELECT * FROM participants WHERE id_participant=".$id_participant;
        $resultat = $bdd->query($stmt);

        if(!$resultat){
            redirigerVersPageErreur(500, $current_url);
        }
        $infos_participant = $resultat->fetch(PDO::FETCH_ASSOC);
        $resultat->closeCursor();
    }else{
        redirigerVersPageErreur(404, $current_url);
    }
}

// Inclusion des informations générales
require_once('includes/entete_infos_generales.php');

// Inclusion des informations bancaires
$page_modification = true;
require_once('includes/entete_infos_bancaires.php');

/** Validation des informations reçues */

if(isset($_POST['modifier_infos'])){
    // Traitement des informations textuelles
    require_once('includes/validation_infos_generales.php');
    // Traitement des fichiers
    require_once('includes/validation_infos_bancaires.php');

    // Mise à jour des données

    if(!isset($erreurs)){

        // Table Participants

        $stmt = $bdd->prepare('UPDATE participants SET nom=:val1, prenoms=:val2, matricule_ifu=:val3, date_naissance=:val4, lieu_naissance=:val5 WHERE id_participant='.$id_participant);

        $stmt->bindParam(':val1', $_POST['nom']);
        $stmt->bindParam(':val2', $_POST['prenoms']);
        $stmt->bindParam(':val3', $_POST['matricule_ifu']);
        $stmt->bindParam(':val4', $_POST['date_naissance']);
        $stmt->bindParam(':val5', $_POST['lieu_naissance']);

        $resultat = $stmt->execute();

        if(!$resultat){
            redirigerVersPageErreur(500, $current_url);
        }

        // On passe aux informations bancaires
        require_once('includes/enregistrement_fichiers.php');

    }
}

if (isset($traitement_fichiers_ok) && $traitement_fichiers_ok) {
    $message_succes = true;
}