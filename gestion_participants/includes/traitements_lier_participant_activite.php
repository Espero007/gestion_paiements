<?php

// je dois avoir tout au moins soit l'id du participant soit celui de l'activité donc si ce n'est pas le cas on redirige purement et simplement

if (!isset($_GET['id_participant']) && !isset($_GET['id_activite'])) {
    header('location:' . $_SESSION['previous_url']);
    exit;
}

// Quelques booléens
$aucune_activite_1 = false; // pas d'activités en bdd
$aucune_activite_2 = false; // pas d'activités non associés au participant

if (isset($_GET['id_participant']) && !isset($_GET['id_activite'])) {
    //Participant vers activité
    $sens = 0;

    // Assurons-nous que l'id du participant est valide
    if (valider_id('get', 'id_participant', $bdd, 'participants')) {
        $id_participant = $_GET['id_participant'];

        // On vérifie s'il y a des activités en bdd
        $stmt = $bdd->query('SELECT * FROM activites WHERE id_user=' . $_SESSION['user_id']);
        if($stmt->rowCount() == 0){
            $aucune_activite_1 = true;
        }else{
            // J'ai besoin des activités auxquelles le participant n'est pas encore associé

            $stmt = $bdd->prepare('
            SELECT id, nom, date_debut, date_fin, description
            FROM activites
            WHERE id_user =' . $_SESSION['user_id'] . '
            AND id NOT IN (SELECT id_activite FROM participations WHERE id_participant=' . $id_participant . ')');
            $stmt->execute();
            $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if(count($activites) == 0){
                $aucune_activite_2 = true; // Il n'y a plus d'activités auxquelles le participant ne soit pas associé
            }
        }
       
    } else {
        header('location:voir_participants.php');
        exit;
    }
}

$aucun_participant_1 = false; // pas de participants en bdd
$aucun_participant_2 = false; // pas de participants non associés à l'activité

if (isset($_GET['id_activite']) && !isset($_GET['id_participant'])) {
    // Activité vers participant
    $sens = 1;

    if (valider_id('get', 'id_activite', $bdd, 'activites')) {
        $id_activite = $_GET['id_activite'];

        // Je dois tout d'abord vérifier qu'il y a des participants
        $stmt= $bdd->query('SELECT * FROM participants WHERE id_user='.$_SESSION['user_id']);
        if($stmt->rowCount() == 0){
            // Pas de participants en bdd
            $aucun_participant_1 = true;
        }else{
            // J'ai besoin des participants qui ne sont pas encore associés à l'activité
            $stmt = $bdd->prepare('
            SELECT id_participant, nom, prenoms, matricule_ifu
            FROM participants
            WHERE id_user =' . $_SESSION['user_id'] . '
            AND id_participant NOT IN (SELECT id_participant FROM participations WHERE id_activite=' . $id_activite . ')');
            $stmt->execute();
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($participants) == 0) {
                $aucun_participant_2 = true;
            }
        }
        
    } else {
        header('location:voir_activites.php');
        exit;
    }
}

if (isset($_GET['id_participant']) && isset($_GET['id_activite'])) {
    // Je vérifie tout d'abord si les deux identifiants sont valides puis je vérifie si ce couple est déjà présent ou non dans la table participations
    $redirect = true;

    if (valider_id('get', 'id_participant', $bdd, 'participants') && valider_id('get', 'id_activite', $bdd, 'activites')) {
        $id_participant = $_GET['id_participant'];
        $id_activite = $_GET['id_activite'];
        $stmt = $bdd->query('SELECT id FROM participations WHERE id_participant=' . $id_participant . ' AND id_activite=' . $id_activite);

        if ($stmt->rowCount() == 0) {
            $redirect = false;

            // Récupérons le type de l'activité
            $type_activite = $bdd->query('SELECT type_activite FROM activites WHERE id=' . $id_activite);
            $type_activite = $type_activite->fetchAll(PDO::FETCH_ASSOC);
            $type_activite = $type_activite[0]['type_activite'];

            $champs_attendus = ['titre', 'compte_bancaire'];
            if ($type_activite != 1) {
                $champs_attendus[] = 'nbr_jours';
                $champs_attendus[] = 'nbr_taches';
            }

            // On va récupérer les titres associés à l'activité actuelle
            $stmt = $bdd->prepare('SELECT id_titre, nom FROM titres WHERE id_activite=' . $id_activite);
            $stmt->execute();
            $titres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($titres as $index => $titre) {
                $titres_intitules[] = $titre['nom'];
            }

            // On récupère aussi les comptes bancaires du participant
            $stmt = $bdd->prepare('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant=' . $id_participant);
            $stmt->execute();
            $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($comptes as $index => $compte) {
                $id_comptes[] = $compte['id'];
            }

            #On entame les validations pour achever la liaison

            if (isset($_POST['lier'])) {

                foreach ($champs_attendus as $champ) {
                    if (!isset($_POST[$champ]) || (isset($_POST[$champ]) && empty($_POST[$champ]))) {
                        if ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
                            $erreurs[$champ][] = "Veuillez indiquer une valeur";
                        } else {
                            $erreurs[$champ][] = "Veuillez sélectionner une valeur";
                        }
                    } else {
                        $valeur = $_POST[$champ];
                        // Les champs sont présents
                        if ($champ == 'titre') {
                            // On s'assure que le titre indiqué est bien dans les titres attendus
                            if (!in_array($valeur, $titres_intitules)) {
                                $erreurs[$champ][] = "Le titre que vous avez choisi n'est pas valide";
                            }
                        } else if ($champ == 'compte_bancaire') {
                            // On s'assure que le compte indiqué est bien présent dans les comptes bancaires récupérés en bdd
                            if (!in_array($valeur, $id_comptes)) {
                                $erreurs[$champ][] = "Le compte bancaire sélectionné n'est pas valide";
                            }
                        } else if ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
                            // On s'assure que la valeur qu'on a reçue est bien un nombre et puis c'est tout je pense
                            if (!filter_input(INPUT_POST, $champ, FILTER_VALIDATE_INT)) {
                                $erreurs[$champ][] = "Vous devez indiquer un chiffre ou un nombre";
                            }
                        }
                    }
                }

                // Liaison effective après les validations diverses

                if (!isset($erreurs)) {
                    // Les manipulations se feront sur la table 'participations' essentiellement
                    $stmt = $bdd->prepare('INSERT INTO participations(id_participant, id_activite, id_titre, id_compte_bancaire, nombre_jours, nombre_taches) VALUES (:val1, :val2, :val3, :val4, :val5, :val6)');
                    $stmt->bindParam(':val1', $id_participant, PDO::PARAM_INT);
                    $stmt->bindParam(':val2', $id_activite, PDO::PARAM_INT);

                    // On récupère l'id du titre qui a été sélectionné
                    foreach ($titres as $titre) {
                        if ($titre['nom'] == $_POST['titre']) {
                            $id_titre = $titre['id_titre'];
                        }
                    }

                    $stmt->bindParam(':val3', $id_titre, PDO::PARAM_INT);
                    $stmt->bindParam(':val4', $_POST['compte_bancaire'], PDO::PARAM_INT);

                    if ($type_activite == 1) {
                        $stmt->bindValue(':val5', null, PDO::PARAM_NULL);
                        $stmt->bindValue(':val6', null, PDO::PARAM_NULL);
                    } else {
                        // Type 2 ou 3
                        $stmt->bindParam(':val5', $_POST['nbr_jours'], PDO::PARAM_INT);
                        $stmt->bindParam(':val6', $_POST['nbr_taches'], PDO::PARAM_INT);
                    }

                    $stmt->execute();

                    // On redirige vers la page d'affichage de tous les participants. Je peux aussi rediriger vers la page de gestion du participant mais bon on va faire ça pour commencer
                    $_SESSION['liaison_reussie'] = true;
                    header('location:voir_participants.php');
                    exit;
                }
            }
        }
    }

    if ($redirect) {
        header('location:/index.php');
        exit;
    }
}

// Si l'un des deux est présent mais n'est pas valide  on redirige vers la page d'erreur.

// J'ai à présent soit l'id de l'activité soit celui du participant
// if(isset($_GET['']))



// // Je baserai le travail sur la notion de sens. La liaison se fera avec cette même page mais selon qu'on vienne de l'activité vers les participants ou du participant vers l'activité l'apparence doit changer, d'où la nécessité de travailler en fonction du sens de la liaison

// // Sens sera une valeur entière : 0 pour le sens intuitif (activités vers participant) et 1 pour participant vers activité


// if (!isset($_GET['sens']) || !filter_input(INPUT_GET, 'sens', FILTER_VALIDATE_INT)) {
//     header('location:' . $_SESSION['previous_url']);
//     exit;
// } else {
//     $sens = $_GET['sens'];
//     $redirect = true;
//     $etape1 = false;
//     $etape2 = false;

//     if ($sens == 1) {
//         // Je suis supposé avoir l'id du participant en premier

//         if (valider_id('get', 'id_participant', $bdd, 'participants')) {
            
//             $id_participant = $_GET['id_participant']; // La valeur indiquée est bien présente dans la table 'participants', on la récupère

//             // Je récupère toutes les activités auxquelles le participant n'est pas encore associé

//             $stmt = $bdd->prepare('
//             SELECT id, nom, date_debut, date_fin, description
//             FROM activites
//             WHERE id_user =' . $_SESSION['user_id'] . '
//             AND id NOT IN (SELECT id_activite FROM participations WHERE id_participant=' . $id_participant . ')');
//             $stmt->execute();
//             $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

//             foreach ($activites as $activite) {
//                 $activites_autorisees[] = $activite['id'];
//             }

//             if (isset($_GET['id_activite'])) {
//                 if (valider_id('get', 'id_activite', $bdd, 'activites')) {
//                     // L'activite est présente et valide donc on poursuit le game et on s'assure que l'activité à laquelle on veut associer le participant n'est pas invalide
//                     if (in_array($_GET['id_activite'], $activites_autorisees)) {
//                         $id_activite = $_GET['id_activite'];
//                         $etape2 = true;
//                         $etape1 = false;
//                         $redirect = false;
//                     } else {
//                         $_SESSION['liaison_non_autorisee'] = true;
//                         header('location:voir_participants.php');
//                         exit;
//                     }
//                 }
//             } else {
//                 $redirect = false;
//                 $etape1 = true;
//                 $etape2 = false;
//             }

//             // if (isset($_GET['id_activite'])) {

//             //     if (valider_id('get', 'id_activite', $bdd, 'activites')) {
//             //         // L'activite est présente et valide donc on poursuit le game et on s'assure que l'activité à laquelle on veut associer le participant n'est pas invalide
//             //         if (in_array($_GET['id_activite'], $activites_autorisees)) {
//             //             $id_activite = $_GET['id_activite'];
//             //             // Récupérons le type de l'activité
//             //             $type_activite = $bdd->query('SELECT type_activite FROM activites WHERE id=' . $id_activite);
//             //             $type_activite = $type_activite->fetchAll(PDO::FETCH_ASSOC);
//             //             $type_activite = $type_activite[0]['type_activite'];

//             //             $champs_attendus = ['titre', 'compte_bancaire'];

//             //             if ($type_activite != 1) {
//             //                 $champs_attendus[] = 'nbr_jours';
//             //                 $champs_attendus[] = 'nbr_taches';
//             //             }

//             //             $redirect = false;

//             //             // On récupère les titres associés à l'activité actuelle
//             //             $stmt = $bdd->prepare('SELECT id_titre, nom FROM titres WHERE id_activite=' . $id_activite);
//             //             $stmt->execute();
//             //             $titres = $stmt->fetchAll(PDO::FETCH_ASSOC);

//             //             foreach ($titres as $index => $titre) {
//             //                 $titres_intitules[] = $titre['nom'];
//             //             }

//             //             // On récupère aussi les comptes bancaires du participant
//             //             $stmt = $bdd->prepare('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant=' . $id_participant);
//             //             $stmt->execute();
//             //             $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

//             //             foreach ($comptes as $index => $compte) {
//             //                 $id_comptes[] = $compte['id'];
//             //             }
//             //         } else {
//             //             $_SESSION['liaison_non_autorisee'] = true;
//             //             header('location:voir_participants.php');
//             //             exit;
//             //         }
//             //     }
//             // } else {
//             //     $redirect = false;
//             //     $etape1 = true;
//             //     $etape2 = false;
//             // }
//         }

//         if ($redirect) {
//             header('location:voir_participants.php');
//             exit;
//         }

//     } else if ($sens == 0) {
//         // Je suis supposé avoir l'id de l'activité en premier donc c'est lui que je vais valider
//         $redirect = true;

//         if(isset($_GET['id_activite'])){
//             // On vérifie la validité de l'id détectée
            
//             if(valider_id('get', 'id_activite', $bdd, 'activites')){
//                 // L'activité existe bien donc on récupère tous les participants qui ne sont pas encore liés à cette activité

//             }

//         }

//         if($redirect){
//             header('location:voir_activites.php');
//             exit;
//         }
//     }else{
//         header('location:'.$_SESSION['previous_url']);
//         exit;
//     }

//     // Arrivé jusqu'ici c'est qu'il n'y a pas eu de redirection et que j'ai l'id du participant et celui de l'activité
    
//     if ($etape2 && !$etape1) {
//         // J'ai l'id du participant et l'id de l'activité. Je suis effectivement à l'étape 2
        
//         // Récupérons le type de l'activité
//         $type_activite = $bdd->query('SELECT type_activite FROM activites WHERE id=' . $id_activite);
//         $type_activite = $type_activite->fetchAll(PDO::FETCH_ASSOC);
//         $type_activite = $type_activite[0]['type_activite'];

//         $champs_attendus = ['titre', 'compte_bancaire'];
//         if ($type_activite != 1) {
//             $champs_attendus[] = 'nbr_jours';
//             $champs_attendus[] = 'nbr_taches';
//         }
        
//         // On va récupérer les titres associés à l'activité actuelle
//         $stmt = $bdd->prepare('SELECT id_titre, nom FROM titres WHERE id_activite=' . $id_activite);
//         $stmt->execute();
//         $titres = $stmt->fetchAll(PDO::FETCH_ASSOC);

//         foreach ($titres as $index => $titre) {
//             $titres_intitules[] = $titre['nom'];
//         }

//         // On récupère aussi les comptes bancaires du participant
//         $stmt = $bdd->prepare('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant=' . $id_participant);
//         $stmt->execute();
//         $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

//         foreach ($comptes as $index => $compte) {
//             $id_comptes[] = $compte['id'];
//         }

//         #On entame les validations pour achever la liaison

//         if (isset($_POST['lier'])) {

//             foreach ($champs_attendus as $champ) {
//                 if (!isset($_POST[$champ]) || (isset($_POST[$champ]) && empty($_POST[$champ]))) {
//                     if ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
//                         $erreurs[$champ][] = "Veuillez indiquer une valeur";
//                     } else {
//                         $erreurs[$champ][] = "Veuillez sélectionner une valeur";
//                     }
//                 } else {
//                     $valeur = $_POST[$champ];
//                     // Les champs sont présents
//                     if ($champ == 'titre') {
//                         // On s'assure que le titre indiqué est bien dans les titres attendus
//                         if (!in_array($valeur, $titres_intitules)) {
//                             $erreurs[$champ][] = "Le titre que vous avez choisi n'est pas valide";
//                         }
//                     } else if ($champ == 'compte_bancaire') {
//                         // On s'assure que le compte indiqué est bien présent dans les comptes bancaires récupérés en bdd
//                         if (!in_array($valeur, $id_comptes)) {
//                             $erreurs[$champ][] = "Le compte bancaire sélectionné n'est pas valide";
//                         }
//                     } else if ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
//                         // On s'assure que la valeur qu'on a reçue est bien un nombre et puis c'est tout je pense
//                         if (!filter_input(INPUT_POST, $champ, FILTER_VALIDATE_INT)) {
//                             $erreurs[$champ][] = "Vous devez indiquer un chiffre ou un nombre";
//                         }
//                     }
//                 }
//             }

//             // Liaison effective après les validations diverses

//             if (!isset($erreurs)) {
//                 // Les manipulations se feront sur la table 'participations' essentiellement
//                 $stmt = $bdd->prepare('INSERT INTO participations(id_participant, id_activite, id_titre, id_compte_bancaire, nombre_jours, nombre_taches) VALUES (:val1, :val2, :val3, :val4, :val5, :val6)');
//                 $stmt->bindParam(':val1', $id_participant, PDO::PARAM_INT);
//                 $stmt->bindParam(':val2', $id_activite, PDO::PARAM_INT);

//                 // On récupère l'id du titre qui a été sélectionné
//                 foreach ($titres as $titre) {
//                     if ($titre['nom'] == $_POST['titre']) {
//                         $id_titre = $titre['id_titre'];
//                     }
//                 }

//                 $stmt->bindParam(':val3', $id_titre, PDO::PARAM_INT);
//                 $stmt->bindParam(':val4', $_POST['compte_bancaire'], PDO::PARAM_INT);

//                 if ($type_activite == 1) {
//                     $stmt->bindValue(':val5', null, PDO::PARAM_NULL);
//                     $stmt->bindValue(':val6', null, PDO::PARAM_NULL);
//                 } else {
//                     // Type 2 ou 3
//                     $stmt->bindParam(':val5', $_POST['nbr_jours'], PDO::PARAM_INT);
//                     $stmt->bindParam(':val6', $_POST['nbr_taches'], PDO::PARAM_INT);
//                 }

//                 $stmt->execute();

//                 // On redirige vers la page d'affichage de tous les participants. Je peux aussi rediriger vers la page de gestion du participant mais bon on va faire ça pour commencer
//                 $_SESSION['liaison_reussie'] = true;
//                 header('location:voir_participants.php');
//                 exit;
//             }
//         }
//     }
// }
