<?php

$type_activite = $bdd->query('SELECT type_activite FROM activites WHERE id=' . $id_activite);
$type_activite = $type_activite->fetchAll(PDO::FETCH_ASSOC);
$type_activite = $type_activite[0]['type_activite'];

$champs_attendus = ['titre', 'compte_bancaire', 'nbr_jours'];

if ($type_activite == 3) {
    $champs_attendus[] = 'nbr_taches';
}

// On va récupérer les titres associés à l'activité actuelle
$stmt = $bdd->prepare('SELECT id_titre, nom FROM titres WHERE id_activite=' . $id_activite);
$stmt->execute();
$titres = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($titres as $index => $titre) {
    $titres_intitules[] = $titre['nom'];
}

for ($i = 0; $i < count($participants); $i++) {
    // On récupère les comptes bancaires du participant
    $stmt = $bdd->prepare('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant=' . $participants[$i]['id_participant']);
    $stmt->execute();
    $comptes[] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comptes[count($comptes) - 1] as $compte_participant) {
        $id_comptes[$i][] = $compte_participant['id'];
    }
}