<?php

// je dois avoir tout au moins soit l'id du participant soit celui de l'activité donc si ce n'est pas le cas on redirige purement et simplement

if (!isset($_GET['id_participant']) && !isset($_GET['id_activite']) && !isset($_GET['modifier'])) {
    header('location:' . $_SESSION['previous_url']);
    exit;
}

// Quelques booléens
$aucune_activite_1 = false; // pas d'activités en bdd
$aucune_activite_2 = false; // pas d'activités non associés au participant

// if (isset($_GET['id_participant']) && !isset($_GET['id_activite'])) {
//     //Participant vers activité
//     $sens = 0;

//     // Assurons-nous que l'id du participant est valide
//     if (valider_id('get', 'id_participant', $bdd, 'participants')) {
//         $id_participant = $_GET['id_participant'];

//         // On vérifie s'il y a des activités en bdd
//         $stmt = $bdd->query('SELECT * FROM activites WHERE id_user=' . $_SESSION['user_id']);
//         if ($stmt->rowCount() == 0) {
//             $aucune_activite_1 = true;
//         } else {
//             // J'ai besoin des activités auxquelles le participant n'est pas encore associé

//             $stmt = $bdd->prepare('
//             SELECT id, nom, date_debut, date_fin, description
//             FROM activites
//             WHERE id_user =' . $_SESSION['user_id'] . '
//             AND id NOT IN (SELECT id_activite FROM participations WHERE id_participant=' . $id_participant . ')');
//             $stmt->execute();
//             $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

//             if (count($activites) == 0) {
//                 $aucune_activite_2 = true; // Il n'y a plus d'activités auxquelles le participant ne soit pas associé
//             }
//         }
//     } else {
//         header('location:voir_participants.php');
//         exit;
//     }
// }

$aucun_participant_1 = false; // pas de participants en bdd
$aucun_participant_2 = false; // pas de participants non associés à l'activité

if (isset($_GET['id_activite']) && !isset($_GET['id_participant'])) {
    // Activité vers participants (on vient de la page de gestion d'une activité en particulier et on doit choisir les participants qui devront être associés à l'activité en question)
    $sens = 1;

    // La procédure se scinde en deux étapes : la première (étape 1) on choisit les acteurs et la seconde consiste à spécifier les informations de liaison entre ces acteurs et l'activité. J'ai préféré faire les deux sur la même page en changeant le contenu par du code PHP et ça fonctionne pour l'instant donc on va garder cette logique
    $etape_1 = true;
    $etape_2 = false;
    if (valider_id('get', 'id_activite', $bdd, 'activites')) {
        $id_activite = $_GET['id_activite'];

        if (!isset($_POST['continuer'])) {
            // Je suis encore à l'étape 1 où je dois récupérer et afficher la liste des participants
            // Je vérifie tout d'abord qu'il y a des participants
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
                    // Pas de participants qui ne soit pas encore associé à l'activité
                    $aucun_participant_2 = true;
                }
            }
        } else {
            // J'ai l'id de l'activité dans l'url et dans la post la variable 'continuer' donc je suis normalement à l'étape 2 et le but ici est de valider les ids des participants sélectionnés afin de m'en servir pour afficher les informations de chacun des participants. Le but ici n'est pas de valider les informations de l'étape 2, garde le à l'esprit
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
                            redirigerVersPageErreur(404, $_SESSION['previous_url']);
                        }

                        $stmt = $bdd->query('SELECT id_participant, nom, prenoms FROM participants WHERE id_participant=' . $id_participant);
                        $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $participants[] = $resultat[0];
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
                            $_SESSION['liaison_reussie'] = 'Les acteurs ont été associés à l\'activité avec succès';
                        } else {
                            $_SESSION['liaison_reussie'] = 'L\'acteur a été associé à l\'activité avec succès';
                        }

                        // header('location:' . $_SESSION['previous_url']);
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

if (isset($_GET['modifier'])) {
    if (!valider_id('get', 'modifier', '', 'participations')) {
        redirigerVersPageErreur(404, $_SESSION['previous_url']);
    }

    $etape_1 = false;
    $etape_2 = true;
    $id_participation = $_GET['modifier'];
    $modification = true;

    $stmt = $bdd->query('SELECT id_activite, id_participant FROM participations WHERE id=' . $id_participation);
    $resultats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();
    $id_activite = $resultats['id_activite'];
    $id_participant = $resultats['id_participant'];

    // On récupère les informations du participant
    $stmt = $bdd->query('SELECT * FROM participants WHERE id_participant=' . $id_participant);
    $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $participants[] = $resultat[0];

    // On récupère les informations de la liaison
    $stmt = $bdd->query('
    SELECT t.nom as titre_liaison, ib.numero_compte, p.nombre_jours, p.nombre_taches
    FROM participations p
    INNER JOIN titres t ON p.id_titre = t.id_titre
    INNER JOIN informations_bancaires ib ON p.id_compte_bancaire = ib.id
    WHERE p.id=' . $id_participation);

    $infos_liaison = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // On récupère les infos comme les titres, les diplomes et etc.
    require_once('infos_intermediaires.php');

    // On passe aux validations et etc.

    if (isset($_POST['enregistrer'])) {
        require_once('validations_liaison.php');

        // Les validations sont ok

        if (!isset($erreurs)) {
            // On débute les actions de modification
            for ($i = 0; $i < count($participants); $i++) {
                $stmt = $bdd->prepare('UPDATE participations SET id_titre=:id_titre, id_compte_bancaire=:id_compte_bancaire, nombre_jours=:nbr_jours, nombre_taches=:nbr_taches WHERE id='.$id_participation);

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
            $_SESSION['modification_reussie'] = 'Les informations ont été mises à jour avec succès !';
            header('location:/gestion_activites/gerer_activite.php?id=' . $id_activite);
            exit;
        }
    }
}

// if (isset($_GET['id_participant']) && isset($_GET['id_activite'])) {
//     // Je vérifie tout d'abord si les deux identifiants sont valides puis je vérifie si ce couple est déjà présent ou non dans la table participations
//     $redirect = true;

//     if (valider_id('get', 'id_participant', $bdd, 'participants') && valider_id('get', 'id_activite', $bdd, 'activites')) {
//         $id_participant = $_GET['id_participant'];
//         $id_activite = $_GET['id_activite'];

//         $stmt = $bdd->query('SELECT id FROM participations WHERE id_participant=' . $id_participant . ' AND id_activite=' . $id_activite);

//         if ($stmt->rowCount() == 0) {
//             $redirect = false;

//             // Récupérons le type de l'activité
//             $type_activite = $bdd->query('SELECT type_activite FROM activites WHERE id=' . $id_activite);
//             $type_activite = $type_activite->fetchAll(PDO::FETCH_ASSOC);
//             $type_activite = $type_activite[0]['type_activite'];

//             $champs_attendus = ['titre', 'compte_bancaire'];
//             if ($type_activite != 1) {
//                 $champs_attendus[] = 'nbr_jours';
//                 $champs_attendus[] = 'nbr_taches';
//             }

//             // On va récupérer les titres associés à l'activité actuelle
//             $stmt = $bdd->prepare('SELECT id_titre, nom FROM titres WHERE id_activite=' . $id_activite);
//             $stmt->execute();
//             $titres = $stmt->fetchAll(PDO::FETCH_ASSOC);

//             foreach ($titres as $index => $titre) {
//                 $titres_intitules[] = $titre['nom'];
//             }

//             // On récupère aussi les comptes bancaires du participant
//             $stmt = $bdd->prepare('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant=' . $id_participant);
//             $stmt->execute();
//             $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

//             foreach ($comptes as $index => $compte) {
//                 $id_comptes[] = $compte['id'];
//             }

//             #On entame les validations pour achever la liaison

//             if (isset($_POST['lier'])) {

//                 foreach ($champs_attendus as $champ) {
//                     if (!isset($_POST[$champ]) || (isset($_POST[$champ]) && empty($_POST[$champ]))) {
//                         if ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
//                             $erreurs[$champ][] = "Veuillez indiquer une valeur";
//                         } else {
//                             $erreurs[$champ][] = "Veuillez sélectionner une valeur";
//                         }
//                     } else {
//                         $valeur = $_POST[$champ];
//                         // Les champs sont présents
//                         if ($champ == 'titre') {
//                             // On s'assure que le titre indiqué est bien dans les titres attendus
//                             if (!in_array($valeur, $titres_intitules)) {
//                                 $erreurs[$champ][] = "Le titre que vous avez choisi n'est pas valide";
//                             }
//                         } else if ($champ == 'compte_bancaire') {
//                             // On s'assure que le compte indiqué est bien présent dans les comptes bancaires récupérés en bdd
//                             if (!in_array($valeur, $id_comptes)) {
//                                 $erreurs[$champ][] = "Le compte bancaire sélectionné n'est pas valide";
//                             }
//                         } else if ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
//                             // On s'assure que la valeur qu'on a reçue est bien un nombre et puis c'est tout je pense
//                             if (!filter_input(INPUT_POST, $champ, FILTER_VALIDATE_INT)) {
//                                 $erreurs[$champ][] = "Vous devez indiquer un chiffre ou un nombre";
//                             }
//                         }
//                     }
//                 }

//                 // Liaison effective après les validations diverses

//                 if (!isset($erreurs)) {
//                     // Les manipulations se feront sur la table 'participations' essentiellement
//                     $stmt = $bdd->prepare('INSERT INTO participations(id_participant, id_activite, id_titre, id_compte_bancaire, nombre_jours, nombre_taches) VALUES (:val1, :val2, :val3, :val4, :val5, :val6)');
//                     $stmt->bindParam(':val1', $id_participant, PDO::PARAM_INT);
//                     $stmt->bindParam(':val2', $id_activite, PDO::PARAM_INT);

//                     // On récupère l'id du titre qui a été sélectionné
//                     foreach ($titres as $titre) {
//                         if ($titre['nom'] == $_POST['titre']) {
//                             $id_titre = $titre['id_titre'];
//                         }
//                     }

//                     $stmt->bindParam(':val3', $id_titre, PDO::PARAM_INT);
//                     $stmt->bindParam(':val4', $_POST['compte_bancaire'], PDO::PARAM_INT);

//                     if ($type_activite == 1) {
//                         $stmt->bindValue(':val5', null, PDO::PARAM_NULL);
//                         $stmt->bindValue(':val6', null, PDO::PARAM_NULL);
//                     } else {
//                         // Type 2 ou 3
//                         $stmt->bindParam(':val5', $_POST['nbr_jours'], PDO::PARAM_INT);
//                         $stmt->bindParam(':val6', $_POST['nbr_taches'], PDO::PARAM_INT);
//                     }

//                     $stmt->execute();

//                     // On redirige vers la page d'affichage de tous les participants. Je peux aussi rediriger vers la page de gestion du participant mais bon on va faire ça pour commencer
//                     $_SESSION['liaison_reussie'] = true;
//                     header('location:voir_participants.php');
//                     exit;
//                 }
//             }
//         }
//     }

//     if ($redirect) {
//         header('location:/index.php');
//         exit;
//     }
// }

/* La sélection de plusieurs participants à la fois */