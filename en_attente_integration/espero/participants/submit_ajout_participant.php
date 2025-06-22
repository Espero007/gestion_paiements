<?php
session_start();
require_once(__DIR__ . '/../../../includes/bdd.php');
require_once(__DIR__ . '/../../../includes/constantes_utilitaires.php');

// On prend en mémoire l'url actuel pour qu'il puisse tenter d'y accéder s'il le veut
// Récupération du protocole (http ou https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
// Récupération du nom de domaine + port si nécessaire
$host = $_SERVER['HTTP_HOST'];
// Récupération du chemin URI
$request_uri = $_SERVER['REQUEST_URI'];
// URL complète
$current_url = $protocol . $host . $request_uri;


// Inclusion des entêtes
require_once('includes/entetes.php');

/** Validation des informations du formulaire */

if (isset($_POST['ajouter_participant'])) {

    /** Traitement des informations textuelles */
    require_once('includes/validation_infos_generales.php');

    /** Traitement du ou des fichier(s) */

    require_once('includes/validation_infos_bancaires.php');

    /** Préparatifs pour l'enregistrement des données */

    if (!isset($erreurs)) {
        // S'il n'y a aucune erreur tout va bien je présume
        $matricule_ifu = $_POST['matricule_ifu'];

        // Enregistrement des données textuelles

        // Participants

        $stmt = $bdd->prepare("INSERT INTO participants(id_user, nom, prenoms, matricule_ifu, date_naissance, lieu_naissance) VALUES (:val1, :val2, :val3, :val4, :val5, :val6)");

        $stmt->bindParam(':val1', $_SESSION['user_id']);
        $stmt->bindParam(':val2', $_POST['nom']);
        $stmt->bindParam(':val3', $_POST['prenoms']);
        $stmt->bindParam(':val4', $_POST['matricule_ifu']);
        $stmt->bindParam(':val5', $_POST['date_naissance']);
        $stmt->bindParam(':val6', $_POST['lieu_naissance']);

        $resultat = $stmt->execute();

        if (!$resultat) {
            // Une erreur s'est produite lors de l'enregistrement des informations
            redirigerVersPageErreur(500, $current_url);
        }
        // Le premier enregistrement a été effectué

        // Pour la suite j'ai besoin de l'id du participant donc je le récupère
        $id_participant = $bdd->lastInsertId();

        // Table fichiers

        // 1- Je définis le nom qui va s'appliquer à ce fichier
        // 2- J'enregistre le fichier
        // 3- Je sauvegarde son id
        // 4- J'enregistre en même temps les informations dans la table informations_bancaires

        require_once('includes/enregistrement_fichiers.php');
    }
}

if (isset($traitement_fichiers_ok) && $traitement_fichiers_ok) {
    $message_succes = true;
}
