<?php


if (isset($_GET['id_activite'])) {
    // On entame les validations en rapport avec le type de l'activité

    $recuperation_type_activite = true; // Le type est valide
}else{
    // L'id n'a pas encore été envoyé
    $recuperation_type_activite = false;
}
