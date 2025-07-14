<?php
session_start();
require_once(__DIR__.'/../includes/bdd.php');
require_once(__DIR__.'/../includes/constantes_utilitaires.php');

if(!valider_id('get', 'id', '', 'participations')){
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// A ce stade l'id est valide
// $_SESSION['modifier_liaison'] = $_GET['id'];
header('location:lier_participant_activite.php?modifier='.$_GET['id']);