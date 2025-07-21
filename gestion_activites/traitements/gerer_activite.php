<?php

// On vérifie la présence de l'id de l'activité à gérer et si elle n'est pas présente on redirige vers la page précédente
$redirect = true;

if (filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    // On vérifie la présence de l'id indiqué dans la table activités
    $stmt = $bdd->prepare('SELECT * FROM activites WHERE id=' . $_GET['id']);
    $stmt->execute();
    $activite = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($activite) != 0) {
        $activite = $activite[0];
        $id_activite = $activite['id'];
        $redirect = false;
    }
}

if ($redirect) {
    header('location:' . $_SESSION['previous_url']);
    exit;
}

// Champs à ne pas afficher

$champs = ['id', 'type_activite', 'id_user', 'id_note_generatrice'];

if ($activite['type_activite'] == 1) {
    $champs[] = 'frais_deplacement_journalier';
    $champs[] = 'taux_taches';
} else {
    // Type 2 ou 3

    $stmt = $bdd->prepare('SELECT nom, indemnite_forfaitaire FROM titres WHERE id_activite=' . $activite['id']);
    $stmt->execute();
    $indemnites_forfaitaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $indemnite_str = '';
    for ($i = 0; $i < count($indemnites_forfaitaires); $i++) {
        $indemnite_str .= htmlspecialchars($indemnites_forfaitaires[$i]['nom']) . ' (<strong>' . htmlspecialchars($indemnites_forfaitaires[$i]['indemnite_forfaitaire']) . ' FCFA</strong>)';
        if ($i != count($indemnites_forfaitaires) - 1) {
            $indemnite_str .= ', ';
        }
    }
}

// Participants associés : récupération et mise en place de la logique nécessaire pour utiliser la fonction afficherSousFormeTableau

$stmt = $bdd->query('
SELECT p1.id, p1.id_participant, p2.nom, p2.prenoms, p2.matricule_ifu, t.nom as titre, p1.nombre_jours, p1.nombre_taches, ib.banque, ib.numero_compte
FROM participations p1
INNER JOIN participants p2 ON p1.id_participant = p2.id_participant
INNER JOIN titres t ON t.id_titre = p1.id_titre
INNER JOIN informations_bancaires ib ON p1.id_compte_bancaire = ib.id
WHERE p1.id_activite=' . $activite['id']);
$participants_associes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$compteur = 0;

if (count($participants_associes) != 0) {
    $informations[0] = ['Nom', 'Prénoms', 'Titre', 'Nombre de jours'];
    if ($activite['type_activite'] == 3)
        $informations[0][] = 'Nombre de tâches';
    $informations[0][] = 'Compte bancaire';

    foreach ($participants_associes as $participant) {
        $informations[1][] = [$participant['nom'], $participant['prenoms'], $participant['titre'], $participant['nombre_jours']];
        if ($activite['type_activite'] == 3)
            $informations[1][count($informations[1]) - 1][] = $participant['nombre_taches'];
        $informations[1][count($informations[1]) - 1][] = $participant['banque'] . ' (' . $participant['numero_compte'] . ')';

        // Définition des actions possibles par participant
        $informations[2][$compteur][] = [
            'intitule' => 'Modifier',
            'lien' => '/gestion_participants/lier_participant_activite.php?modifier=' . $participant['id'].'&sens=1'
        ];
        $informations[2][$compteur][] = [
            'intitule' => 'Gérer le participant',
            'lien' => '/gestion_participants/gerer_participant.php?id=' . $participant['id_participant']
        ];
        $informations[2][$compteur][] = [
            'intitule' => 'Rompre la liaison',
            'lien' => '#',
            'style' => 'text-danger',
            'dernier' => true,
        ];
        $compteur++;
    }
}
