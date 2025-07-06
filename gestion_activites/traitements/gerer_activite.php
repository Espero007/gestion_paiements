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

// Participants associés

$stmt = $bdd->query('
SELECT p1.id_participant, p2.nom, p2.prenoms, p2.matricule_ifu, t.nom as titre, p1.diplome, p1.nombre_jours, p1.nombre_taches, ib.banque, ib.numero_compte
FROM participations p1
INNER JOIN participants p2 ON p1.id_participant = p2.id_participant
INNER JOIN titres t ON t.id_titre = p1.id_titre
INNER JOIN informations_bancaires ib ON p1.id_compte_bancaire = ib.id
WHERE p1.id_activite='.$activite['id']);
$participants_associes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(count($participants_associes) != 0){
    $informations[0] = ['Nom', 'Prénoms', 'Titre', 'Diplome', 'Nombre de jours', 'Nombre de tâches', 'Compte bancaire'];
    foreach ($participants_associes as $participant) {
        $informations[1][] = [$participant['nom'], $participant['prenoms'], $participant['titre'], $participant['diplome'], $participant['nombre_jours'], $participant['nombre_taches'], $participant['banque'].' ('.$participant['numero_compte'].')'];
        $informations[2][] = '/gestion_participants/gerer_participant.php?id='.$participant['id_participant'];
    }
    // $informations[2] = ['/gestion_participants/']
}