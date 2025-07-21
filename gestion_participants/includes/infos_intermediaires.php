<?php

if ($modification) {
    if ($sens == 0) {
        $stmt = $bdd->query('SELECT nom, prenoms FROM participants WHERE id_participant=' . $id_participant);
        $participant = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        // On récupère le titre de l'activité
        $stmt = $bdd->query('SELECT * FROM activites WHERE id=' . $id_activite);
        $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $activites[] = $resultat[0];
        $activites_id = [$id_activite];
    } elseif ($sens == 1) {
        $stmt = $bdd->query('SELECT * FROM participants WHERE id_participant=' . $id_participant);
        $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $participants[] = $resultat[0];
        
        // On récupère le titre de l'activité
        $stmt = $bdd->query('SELECT nom, type_activite FROM activites WHERE id=' . $id_activite);
        $activite = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    }
}

if ($sens == 1) {
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
} elseif ($sens == 0) {
    foreach ($activites_id as $id_activite) {
        // Titres : en deux temps, d'abord les intitulés et ensuite les ids

        $stmt = $bdd->query('SELECT nom FROM titres WHERE id_activite=' . $id_activite);
        $titres_intitules[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $valeur = count($titres_intitules) - 1;
        foreach ($titres_intitules[$valeur] as $index => $titre) {
            $titres_intitules[$valeur][$index] = $titre['nom'];
        }
        $stmt = $bdd->query('SELECT id_titre FROM titres WHERE id_activite=' . $id_activite);
        $ids_titres[] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $valeur = count($ids_titres) - 1;
        foreach ($ids_titres[$valeur] as $index => $titre) {
            $ids_titres[$valeur][$index] = $titre['id_titre'];
        }
    }

    // Les comptes du participant/acteur

    $stmt = $bdd->query('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant=' . $id_participant);
    $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($comptes as $compte) {
        $ids_comptes[] = $compte['id'];
    }
}
