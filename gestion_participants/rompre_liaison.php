<?php

if(!valider_id('get', 'id', '', 'participations')){
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_participation = $_GET['id'];

// L'id de la participation est valide. Après ça bh je en pense qu'il y ait d'autres validations à faire

$stmt = $bdd->query('DELETE FROM participations WHERE id='.$id_participation);

// Bref c'est fini