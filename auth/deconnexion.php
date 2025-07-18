<?php

session_start();
require_once(__DIR__.'/../includes/bdd.php');

// On vérifie s'il a un cookie actif au vu de la bdd et on le supprime si oui
$stmt = $bdd->query('SELECT * FROM token_souvenir WHERE user_id=' . $_SESSION['user_id']);
if ($stmt->rowCount() != 0) {
    $stmt = $bdd->query('DELETE FROM token_souvenir WHERE user_id='.$_SESSION['user_id']);
}

$_SESSION['deconnexion'] = true;
header('location:connexion.php');