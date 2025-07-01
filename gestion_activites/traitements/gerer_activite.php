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
}else{
    // Type 2 ou 3

    $stmt = $bdd->prepare('SELECT nom, indemnite_forfaitaire FROM titres WHERE id_activite='.$activite['id']);
    $stmt->execute();
    $indemnites_forfaitaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $indemnite_str = '';
    for ($i=0; $i < count($indemnites_forfaitaires); $i++) {
        $indemnite_str .= htmlspecialchars($indemnites_forfaitaires[$i]['nom']) . ' (<strong>' . htmlspecialchars($indemnites_forfaitaires[$i]['indemnite_forfaitaire']) . ' FCFA</strong>)';
        if($i != count($indemnites_forfaitaires) - 1){
            $indemnite_str .= ', ';
        }
    }
}