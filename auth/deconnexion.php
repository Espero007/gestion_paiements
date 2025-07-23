<?php

session_start();
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');

// On vérifie s'il a un cookie actif au vu de la bdd et on le supprime si oui
$stmt = $bdd->query('SELECT * FROM token_souvenir WHERE user_id=' . $_SESSION['user_id']);
if ($stmt->rowCount() != 0) {
    $stmt = $bdd->query('DELETE FROM token_souvenir WHERE user_id=' . $_SESSION['user_id']);
    unset($_COOKIE['souvenir_' . $_SESSION['nom_cookie']]);
    setcookie('souvenir_' . $_SESSION['nom_cookie'], '', time() - 3600, '/', '', false, true); // Supprimer le cookie
}

$_SESSION['deconnexion'] = true;
header('location:/auth/connexion.php');
