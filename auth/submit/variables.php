<?php
require_once(__DIR__ . "/../../includes/bdd.php");
// Récupération des variables à l'aide du client MySQL
$activityStatement = $bdd->prepare('SELECT * FROM activites');
$activityStatement->execute();
$activity  = $activityStatement->fetchAll();

$connect = $bdd->prepare("SELECT * FROM connexion");
$connect->execute();
$connexion = $connect->fetchAll();
?>