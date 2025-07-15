<?php

session_start();
require_once(__DIR__.'/../includes/bdd.php');
if(isset($_COOKIE['souvenir'])){
    $token = hash('sha256', $_COOKIE['souvenir']) ;
    $smt = $bdd->prepare("DELETE FROM token_souvenir WHERE token = ?" );

    $smt->execute([$token]);

    setcookie('souvenir','', time() - 3600, '/');
}

$_SESSION['deconnexion'] = true;
header('location:connexion.php');