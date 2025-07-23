<?php

// On a au moins l'un des trois
$modification = false;
// Posons quelques booléens utiles pour l'étape 2 où on prendra réellement les valeurs
// Sens 0
$aucune_activite_1 = false; // pas d'activités dans la bdd
$aucune_activite_2 = false; // pas d'activités non associées à l'acteur
// Sens 1
$aucun_participant_1 = false; // pas de participants en bdd
$aucun_participant_2 = false; // pas de participants non associés à l'activité
// Pour gérer l'affichage de la page
$etape_1 = false;
$etape_2 = false;

// Occupons nous en premier de la partie Participant vers Activités que je pose comme étant le sens 0

if ($sens == 0 && isset($_GET['id'])) {
    // $sens = 0;
    $etape_1 = true;

    // On s'assure tout d'abord que l'id du participant est valide

    if (valider_id('get', 'id', '', 'participants')) {
        $id_participant = dechiffrer($_GET['id']);

        // On récupère les informations du gars s'il en a eu entre temps
        $stmt = $bdd->query(
            '
        SELECT t.nom as titre_liaison, ib.numero_compte, ib.id as compte_bancaire, p.nombre_jours as nbr_jours, p.nombre_taches as nbr_taches, p.id_participant, p.id_activite
        FROM participations p
        INNER JOIN titres t ON p.id_titre = t.id_titre
        INNER JOIN informations_bancaires ib ON p.id_compte_bancaire = ib.id
        WHERE p.id_participant=' . $id_participant .
                ' ORDER BY p.id DESC'
        );

        if ($stmt->rowCount() != 0) {
            $derniere_liaison = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $derniere_liaison = [];
        }

        $stmt->closeCursor();

        if ($etape_1 && !isset($_POST['continuer'])) {
            // Nous sommes encore à l'étape 1
            // On vérifie s'il y a des activités en bdd
            $stmt = $bdd->query('SELECT * FROM activites WHERE id_user=' . $_SESSION['user_id']);
            if ($stmt->rowCount() == 0) {
                $aucune_activite_1 = true;
            } else {
                // J'ai besoin des activités auxquelles le participant n'est pas encore associé
                $stmt = $bdd->prepare('
                SELECT id, nom, centre, description
                FROM activites a
                WHERE id_user =' . $_SESSION['user_id'] . '
                AND id NOT IN (SELECT id_activite FROM participations WHERE id_participant=' . $id_participant . ')
                ORDER BY a.nom ASC');
                $stmt->execute();
                $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($activites) == 0) {
                    $aucune_activite_2 = true; // Il n'y a aucune activité à laquelle le participant/l'acteur n'est pas encore associé
                }
            }
        } else {
            // Nous avons les activités sélectionnées donc nous sommes à l'étape 2
            $etape_1 = false;
            $etape_2 = true;

            // On récupère les informations du participant

            $stmt = $bdd->query('SELECT nom, prenoms FROM participants WHERE id_participant=' . $id_participant);
            $participant = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if (!isset($_POST['activites_id'])) {
                redirigerVersPageErreur();
            } else {
                $activites_id = $_POST['activites_id']; // A ce stade, on dispose de la liste des ids mais ils sont chiffrés donc il faut veiller à les déchiffrer
                foreach ($activites_id as $index => $id) {
                    $activites_id[$index] = dechiffrer($id);
                }

                $activites = [];

                foreach ($activites_id as $id) {
                    if (!valider_id(null, null, null, 'activites', $id, false)) {
                        redirigerVersPageErreur(404);
                    } else {
                        $id_activite = $id;
                        // On vérifie si par hasard l'activité en instance et le participant sont déjà liés
                        $stmt = $bdd->query('SELECT id FROM participations WHERE id_participant=' . $id_participant . ' AND id_activite=' . $id_activite);
                        if ($stmt->rowCount() != 0) {
                            redirigerVersPageErreur(404);
                        } else {
                            // On peut à présent récupérer les informations de l'activité qui ici est supposé valide
                            $stmt = $bdd->query('SELECT id, nom, type_activite FROM activites WHERE id=' . $id_activite . ' ORDER BY nom');
                            $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $activites[] = $resultat[0];
                        }
                    }
                }

                require_once('infos_intermediaires.php');

                // On principe avec ce qui a été pris en haut, l'affichage devrait se faire donc on s'attaque à présent aux validations

                if (isset($_POST['lier'])) {
                    /** Validations */
                    require_once('validations_liaison.php');

                    // Liaisons effectives après validations

                    if (!isset($erreurs)) {
                        for ($i = 0; $i < count($activites); $i++) {
                            $activite = $activites[$i];
                            $stmt = $bdd->prepare('INSERT INTO participations(id_participant, id_activite, id_titre, id_compte_bancaire, nombre_jours, nombre_taches) VALUES (:id_participant, :id_activite, :id_titre, :id_compte_bancaire, :nbr_jours, :nbr_taches)');

                            // On récupère l'id du titre qui a été sélectionné
                            for ($j = 0; $j < count($titres_intitules[$i]); $j++) {
                                $titre = $titres_intitules[$i][$j];
                                if ($titre == $_POST['titre'][$i]) {
                                    $id_titre = $ids_titres[$i][$j];
                                }
                            }
                            $stmt->execute([
                                'id_participant' => $id_participant,
                                'id_activite' => $activite['id'],
                                'id_titre' => $id_titre,
                                'id_compte_bancaire' => $_POST['compte_bancaire'][$i],
                                'nbr_jours' => $_POST['nbr_jours'][$i],
                                'nbr_taches' => $activite['type_activite'] == 3 ? $_POST['nbr_taches'][$i] : null
                            ]);
                        }

                        // Redirection
                        if (count($activites) > 1) {
                            $_SESSION['liaison_reussie'] = 'L\'acteur a été associé aux activités avec succès !';
                        } else {
                            $_SESSION['liaison_reussie'] = 'L\'acteur a été associé à l\'activité avec succès !';
                        }

                        header('location:/gestion_participants/gerer_participant.php?id=' . chiffrer($id_participant));
                        exit;
                    }
                }
            }
        }
    } else {
        redirigerVersPageErreur(404);
    }
}

// On s'intéresse à présent au sens 1 : Activité vers Participants

if ($sens == 1 && isset($_GET['id'])) {
    // Activité vers participant
    // $sens = 1;
    $etape_1 = true;

    // On s'assure que l'id de l'activité est valide

    if (valider_id('get', 'id', $bdd, 'activites')) {
        $id_activite = dechiffrer($_GET['id']);

        if ($etape_1 && !isset($_POST['continuer'])) {
            // Nous sommes encore à l'étape 1
            // On vérifie s'il y a des participants en bdd
            $stmt = $bdd->query('SELECT * FROM participants WHERE id_user=' . $_SESSION['user_id']);
            if ($stmt->rowCount() == 0) {
                // Pas de participants en bdd
                $aucun_participant_1 = true;
            } else {
                // Il y a des participants dans la base de données mais j'ai besoin des participants qui ne sont pas encore associés à l'activité
                $stmt = $bdd->prepare('
                SELECT id_participant, nom, prenoms, matricule_ifu
                FROM participants
                WHERE id_user =' . $_SESSION['user_id'] . '
                AND id_participant NOT IN (SELECT id_participant FROM participations WHERE id_activite=' . $id_activite . ') ORDER BY nom');
                $stmt->execute();
                $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($participants) == 0) {
                    // Pas de participants qui ne soit pas encore associé à l'utilisateur
                    $aucun_participant_2 = true;
                }
            }
        } else {
            // J'ai les participants sélectionnés donc je suis à l'étape 2
            $etape_1 = false;
            $etape_2 = true;

            // De suite on rentre dans notre chaîne de validation habituelle

            if (!isset($_POST['participants_id'])) {
                redirigerVersPageErreur(404, $_SESSION['previous_url']);
            } else {
                // Le tableau contenant les id des participants sélectionnés est bien là
                $participants_id = $_POST['participants_id'];
                $participants = []; // Un tableau qui va contenir les informations associées à chaque participant

                // Bouclons donc sur les id de chaque participant pour m'assurer qu'ils sont valides

                foreach ($participants_id as $id) {
                    if (!valider_id(null, null, $bdd, 'participants', $id, false)) {
                        // L'id n'existe pas en bdd
                        redirigerVersPageErreur(404, $_SESSION['previous_url']);
                    } else {
                        // Il existe en bdd. L'id de l'activité est déjà validé si on arrive à ce niveau donc pas besoin de s'en préoccuper
                        $id_participant = $id;

                        // On vérifie si par hasard l'activité et le participant en instance sont déjà liés, soit présents dans la table 'participations'
                        $stmt = $bdd->query('SELECT id FROM participations WHERE id_participant=' . $id_participant . ' AND id_activite=' . $id_activite);

                        if ($stmt->rowCount() != 0) {
                            redirigerVersPageErreur();
                        } else {
                            // On peut à présent récupérer les informations du participant
                            $stmt = $bdd->query('SELECT id_participant, nom, prenoms FROM participants WHERE id_participant=' . $id_participant . ' ORDER BY nom ASC');
                            $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $participants[] = $resultat[0];

                            // On récupère aussi les informations de liaison s'il en a eu

                            $stmt = $bdd->query(
                                '
                            SELECT t.nom as titre_liaison, ib.numero_compte, ib.id as compte_bancaire, p.nombre_jours as nbr_jours, p.nombre_taches as nbr_taches, p.id_participant, p.id_activite
                            FROM participations p
                            INNER JOIN titres t ON p.id_titre = t.id_titre
                            INNER JOIN informations_bancaires ib ON p.id_compte_bancaire = ib.id
                            WHERE p.id_participant=' . $id_participant .
                                    ' ORDER BY p.id DESC'
                            );

                            if ($stmt->rowCount() != 0) {
                                $derniere_liaison[] = $stmt->fetch(PDO::FETCH_ASSOC);
                            } else {
                                $derniere_liaison[] = [];
                            }

                            $stmt->closeCursor();
                        }
                    }
                }

                // A ce stade tout va bien. Je suis dans le sens 1 : de l'activité vers les participants et les ids sont ok donc ici on s'intéresse aux informations à afficher pour l'étape 2 et pour celà on va commencer par récupérer le type de l'activité et ls titres qui lui sont associés

                require_once('infos_intermediaires.php');

                // Les informations à afficher sont récupérées et stockées. On passe à présent aux validations nécessaires lorsque le formulaire sera soumis

                if (isset($_POST['lier'])) {

                    require_once('validations_liaison.php');

                    // Liaison effective après validations

                    if (!isset($erreurs)) {
                        // Insertions dans la table 'participations' pour chaque participant

                        for ($i = 0; $i < count($participants); $i++) {
                            $stmt = $bdd->prepare('INSERT INTO participations(id_participant, id_activite, id_titre, id_compte_bancaire, nombre_jours, nombre_taches) VALUES (:id_participant, :id_activite, :id_titre, :id_compte_bancaire, :nbr_jours, :nbr_taches)');
                            $stmt->bindParam(':id_participant', $participants[$i]['id_participant'], PDO::PARAM_INT);
                            $stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);

                            // On récupère l'id du titre qui a été sélectionné
                            foreach ($titres as $titre) {
                                if ($titre['nom'] == $_POST['titre'][$i]) {
                                    $id_titre = $titre['id_titre'];
                                }
                            }

                            $stmt->bindParam(':id_titre', $id_titre, PDO::PARAM_INT);
                            // $stmt->bindParam(':diplome', $_POST['diplome'][$i]);
                            $stmt->bindParam(':id_compte_bancaire', $_POST['compte_bancaire'][$i], PDO::PARAM_INT);
                            $stmt->bindParam(':nbr_jours', $_POST['nbr_jours'][$i], PDO::PARAM_INT);

                            if ($type_activite == 3) {
                                $stmt->bindParam(':nbr_taches', $_POST['nbr_taches'][$i], PDO::PARAM_INT);
                            } else {
                                $stmt->bindValue(':nbr_taches', null, PDO::PARAM_NULL);
                            }

                            $stmt->execute();
                        }

                        // Redirection en cas de succès
                        if (count($participants) > 1) {
                            $_SESSION['liaison_reussie'] = 'Les acteurs ont été associés à l\'activité avec succès !';
                        } else {
                            $_SESSION['liaison_reussie'] = 'L\'acteur a été associé à l\'activité avec succès !';
                        }
                        header('location:/gestion_activites/gerer_activite.php?id=' . chiffrer($id_activite));
                        exit;
                    }
                }
            }
        }
    } else {
        redirigerVersPageErreur();
    }
}

if (isset($_GET['modifier'])) {
    if (!valider_id('get', 'modifier', '', 'participations')) {
        redirigerVersPageErreur(404, $_SESSION['previous_url']);
    }

    $etape_1 = false;
    $etape_2 = true;
    $id_participation = dechiffrer($_GET['modifier']);
    $modification = true;

    // On récupère les informations de la liaison
    $stmt = $bdd->query('
    SELECT t.nom as titre_liaison, ib.numero_compte, ib.id as compte_bancaire, p.nombre_jours as nbr_jours, p.nombre_taches as nbr_taches, p.id_participant, p.id_activite
    FROM participations p
    INNER JOIN titres t ON p.id_titre = t.id_titre
    INNER JOIN informations_bancaires ib ON p.id_compte_bancaire = ib.id
    WHERE p.id=' . $id_participation);

    $infos_liaison = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $id_activite = $infos_liaison['id_activite'];
    $id_participant = $infos_liaison['id_participant'];

    // On récupère lesinformations intermédiaires
    require_once('infos_intermediaires.php');

    // On passe aux validations et etc.

    if (isset($_POST['enregistrer'])) {
        require_once('validations_liaison.php');

        if (!isset($erreurs)) {
            $modification_effective = false;
            if ($sens == 1) {
                // On checke s'il y a effectivement eu une modification
                $champs_attendus = ['titre', 'compte_bancaire', 'nbr_jours'];
                if ($activite['type_activite'] == 3) {
                    $champs_attendus[] = 'nbr_taches';
                }

                foreach ($champs_attendus as $champ) {
                    $valeur = $_POST[$champ][0];
                    $champ = $champ == 'titre' ? 'titre_liaison' : $champ;
                    if ($valeur != $infos_liaison[$champ]) {
                        $modification_effective = true;
                    }
                }

                // On réalise la mise à jour à présent

                if ($modification_effective) {

                    $stmt = $bdd->prepare('UPDATE participations SET id_titre=:id_titre, id_compte_bancaire=:id_compte_bancaire, nombre_jours=:nbr_jours, nombre_taches=:nbr_taches WHERE id=' . $id_participation);

                    // On récupère l'id du titre qui a été sélectionné
                    foreach ($titres as $titre) {
                        if ($titre['nom'] == $_POST['titre'][0]) {
                            $id_titre = $titre['id_titre'];
                        }
                    }

                    $stmt->bindParam(':id_titre', $id_titre, PDO::PARAM_INT);
                    $stmt->bindParam(':id_compte_bancaire', $_POST['compte_bancaire'][0], PDO::PARAM_INT);
                    $stmt->bindParam(':nbr_jours', $_POST['nbr_jours'][0], PDO::PARAM_INT);

                    if ($type_activite == 3) {
                        $stmt->bindParam(':nbr_taches', $_POST['nbr_taches'][0], PDO::PARAM_INT);
                    } else {
                        $stmt->bindValue(':nbr_taches', null, PDO::PARAM_NULL);
                    }
                    $stmt->execute();

                    $_SESSION['modification_reussie'] = 'Les informations ont été mises à jour avec succès !';
                }
                header('location:/gestion_activites/gerer_activite.php?id=' . chiffrer($id_activite));
                exit;
            } elseif ($sens == 0) {
                // Pour savoir si des modifications ont été effectuées ou pas
                $champs_attendus = ['titre', 'compte_bancaire', 'nbr_jours'];
                if ($activites[0]['type_activite'] == 3) {
                    $champs_attendus[] = 'nbr_taches';
                }

                foreach ($champs_attendus as $champ) {
                    $valeur = $_POST[$champ][0];
                    $champ = $champ == 'titre' ? 'titre_liaison' : $champ;
                    if ($valeur != $infos_liaison[$champ]) {
                        $modification_effective = true;
                    }
                }

                if ($modification_effective) {
                    // On débute la mise à jour en bonne et due forme
                    $stmt = $bdd->prepare('UPDATE participations SET id_titre=:id_titre, id_compte_bancaire=:id_compte_bancaire, nombre_jours=:nbr_jours, nombre_taches=:nbr_taches WHERE id=' . $id_participation);

                    for ($j = 0; $j < count($titres_intitules[0]); $j++) {
                        $titre = $titres_intitules[0][$j];
                        if ($titre == $_POST['titre'][0]) {
                            $id_titre = $ids_titres[0][$j];
                        }
                    }

                    $stmt->execute([
                        'id_titre' => $id_titre,
                        'id_compte_bancaire' => $_POST['compte_bancaire'][0],
                        'nbr_jours' => $_POST['nbr_jours'][0],
                        'nbr_taches' => $activites[0]['type_activite'] == 3 ? $_POST['nbr_taches'][0] : null
                    ]);

                    $_SESSION['modification_reussie'] = 'Les informations ont été mises à jour avec succès !';
                }
                header('location:/gestion_participants/gerer_participant.php?id=' . chiffrer($id_participant));
                exit;
            }
        }
    }


    // On récupère les informations intermédiaires selon le sens
}
