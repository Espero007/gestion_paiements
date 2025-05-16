<?php
session_start();

// On prend en mémoire l'url actuel pour qu'il puisse tenter d'y accéder s'il le veut
// Récupération du protocole (http ou https)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
// Récupération du nom de domaine + port si nécessaire
$host = $_SERVER['HTTP_HOST'];
// Récupération du chemin URI
$request_uri = $_SERVER['REQUEST_URI'];
// URL complète
$current_url = $protocol . $host . $request_uri;

$champs_attendus = [
    "nom" => "Nom"
];

/** Validation des informations du formulaire */

if (isset($_POST['ajouter_participant'])) {
    foreach ($champs_attendus as $champ => $intitule_champ) {
        $valeur_champ = $_POST[$champ];

        // 1- Vérifier tout d'abord la présence de tous les champs attendus

        if(!array_key_exists($champ, $_POST)){
            // Si un champ est manquant, on dirige vers la page 404 tout simplement
            $_SESSION['previous_url'] = $current_url;
            $_SESSION['code_erreur'] = 404;
            header('location:../../erreur.php');
        }else{
            // Le champ ne manque pas à l'appel
            // 2- S'assurer à présent qu'il n'est pas vide

            if(empty($valeur_champ)){
                $erreurs[$champ][] = "Veuillez remplir ce champ";
            }
        }
    }
}