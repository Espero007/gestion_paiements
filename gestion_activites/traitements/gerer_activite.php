<?php

// On vérifie la présence de l'id de l'activité à gérer et si elle n'est pas présente on redirige vers 'voir_activites.php'
$redirect = true;

if(filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)){
    // On vérifie la présence de l'id indiqué dans la table activités
    $stmt = $bdd->prepare('SELECT * FROM activites WHERE id='.$_GET['id']);
    $stmt->execute();
    $activite = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($activite) !=0){
        $activite = $activite[0];
        $redirect = false;
    }
}

if($redirect){
    header('location:voir_activites.php');
    exit;
}

// Champs à ne pas afficher

$champs = ['id', 'type_activite', 'id_user', 'id_note_generatrice'];

if($activite['type_activite'] == 1){
    $champs[] = 'frais_deplacement_journalier';
    $champs[] = 'taux_taches';
}