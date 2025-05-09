<?php

session_start();
require_once('includes/bdd.php');

if (!(isset($_SESSION['user_id']) && isset($_SESSION['nom']) && isset($_SESSION['prenoms']))) {
    // Utilisateur non connecté

    // On le dirige vers la page de connexion en sauvegardant l'url à laquelle il voulait accéder de base. Ainsi on s'arrange pour qu'il revienne sur cette page une fois qu'il se sera connecté

    // Récupération du protocole (http ou https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    // Récupération du nom de domaine + port si nécessaire
    $host = $_SERVER['HTTP_HOST'];

    // Récupération du chemin URI
    $request_uri = $_SERVER['REQUEST_URI'];

    // URL complète
    $current_url = $protocol . $host . $request_uri;

    $_SESSION['previous_url'] = $current_url;

    header('location:/auth/connexion.php');
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Gestionnaire de Paiements - Tableau de bord</title>

    <!-- Custom fonts for this template-->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">

</head>