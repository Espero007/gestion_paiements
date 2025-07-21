<?php

// On a au moins l'un des trois
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

if (isset($_GET['id_participant'])) {
    $sens = 0;
    $etape_1 = true;

    // On s'assure tout d'abord que l'id du participant est valide

    if (valider_id('get', 'id_participant', '', 'participants')) {
        $id_participant = $_GET['id_participant'];

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

            if (!isset($_POST['activites_id'])) {
                redirigerVersPageErreur();
            } else {
                $activites_id = $_POST['activites_id'];
                $activites = [];

                foreach ($activites_id as $id) {
                    if (!valider_id(null, null, null, 'activites', $id)) {
                        redirigerVersPageErreur(404);
                    } else {
                        $id_activite = $id;
                        // On vérifie si par hasard l'activité en instance et le participant sont déjà liés
                        $stmt = $bdd->query('SELECT id FROM participations WHERE id_participant=' . $id_participant . ' AND id_activite=' . $id_activite);
                        if ($stmt->rowCount() != 0) {
                            redirigerVersPageErreur(404);
                        } else {
                            // On peut à présent récupérer les informations de l'activité qui ici est supposé valide
                            $stmt = $bdd->query('SELECT id, nom, type_activite FROM activites WHERE id=' . $id_activite);
                            $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $activites[] = $resultat[0];

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
                    }
                }

                // Les comptes du participant/acteur

                $stmt = $bdd->query('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant=' . $id_participant);
                $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($comptes as $compte) {
                    $ids_comptes[] = $compte['id'];
                }

                // On principe avec ce qui a été pris en haut, l'affichage devrait se faire donc on s'attaque à présent aux validations

                if (isset($_POST['lier'])) {
                    /** Validations */

                    for ($i = 0; $i < count($activites); $i++) {
                        $champs_attendus = ['titre', 'compte_bancaire', 'nbr_jours'];
                        if ($activites[$i]['type_activite'] == 3) {
                            $champs_attendus[] = 'nbr_taches';
                        }

                        foreach ($champs_attendus as $champ) {
                            if (!isset($_POST[$champ][$i]) || (isset($_POST[$champ][$i]) && empty($_POST[$champ][$i]))) {
                                if ($champ == 'compte_bancaire') {
                                    $erreurs[$champ][$i][] = 'Veuillez sélectionner un compte bancaire';
                                } elseif ($champ == 'nbr_taches' && isset($_POST[$champ][$i])) {
                                    if ($_POST[$champ][$i] == 0) {
                                        $erreurs[$champ][$i][] = 'Veuillez indiquer une valeur numérique valide';
                                    }
                                } else {
                                    $erreurs[$champ][$i][] = 'Veuillez remplir ce champ';
                                }
                            } else {
                                $valeur =  $_POST[$champ][$i];

                                if ($champ == 'titre') {
                                    if (!in_array($valeur, $titres_intitules[$i])) {
                                        $erreurs[$champ][$i][] = 'Le titre que vous avez choisi n\'est pas valide';
                                    }
                                } elseif ($champ == 'compte_bancaire') {
                                    if (!in_array($valeur, $ids_comptes)) {
                                        $erreurs[$champ][$i][] = "Le compte bancaire sélectionné n'est pas valide";
                                    }
                                } elseif ($champ == 'nbr_jours' || $champ == 'nbr_taches') {

                                    if (!filter_var($valeur, FILTER_VALIDATE_INT)) {
                                        $erreurs[$champ][$i][] = "Vous devez indiquer une valeur numérique valide";
                                    }
                                }
                            }
                        }
                    }

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

                        header('location:/gestion_participants/gerer_participant.php?id=' . $id_participant);
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

if (isset($_GET['id_activite'])) {
    // Activité vers participant
    $sens = 1;
    $etape_1 = true;

    // On s'assure que l'id de l'activité est valide

    if (valider_id('get', 'id_activite', $bdd, 'activites')) {
        $id_activite = $_GET['id_activite'];

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
                AND id_participant NOT IN (SELECT id_participant FROM participations WHERE id_activite=' . $id_activite . ')');
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
                    if (!valider_id(null, null, $bdd, 'participants', $id)) {
                        // L'id n'existe pas en bdd
                        redirigerVersPageErreur(404, $_SESSION['previous_url']);
                    } else {
                        // Il existe en bdd. L'id de l'activité est déjà validé si on arrive à ce niveau donc pas besoin de s'en préoccuper
                        $id_participant = $id;

                        // On vérifie si par hasard l'activité et le participant en instance sont déjà liés, soit présents dans la table 'participations'
                        $stmt = $bdd->query('SELECT id FROM participations WHERE id_participant=' . $id_participant . ' AND id_activite=' . $id_activite);

                        if ($stmt->rowCount() != 0) {
                            redirigerVersPageErreur();
                        }else{
                            // On peut à présent récupérer les informations du participant
                            $stmt = $bdd->query('SELECT id_participant, nom, prenoms FROM participants WHERE id_participant=' . $id_participant);
                            $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            $participants[] = $resultat[0];
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
                        header('location:/gestion_activites/gerer_activite.php?id=' . $id_activite);
                        exit;
                    }
                }
            }
        }
    } else {
        header('location:' . $_SESSION['previous_url']);
        exit;
    }
}
