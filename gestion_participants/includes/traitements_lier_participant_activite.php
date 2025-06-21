<?php

// Je vais partir du principe que j'ai choisi le participant et que la première chose à faire sur cette page sera de choisir l'activité à laquelle je veux l'associer. Pour l'instant on ne fera la liaison qu'avec une activté à la fois mais quel affichage mettre en place pour les activités ?

// Validations
$redirect = true;

// Je suis supposé recevoir l'id du participant sélectionné donc on va partir dessus et la valider
if (valider_id('get', 'id_participant', $bdd, 'participants')) {
    // La valeur indiquée est bien retrouvée dans la table 'participants' donc pas besoin de redirection je présume.
    $id_participant = $_GET['id_participant'];

    // A présent il faut que je prenne toutes les activités créées par l'utilisateur qui ne sont pas déjà associées au participant pour les afficher
    $stmt = $bdd->prepare('
    SELECT id, nom, date_debut, date_fin, description
    FROM activites
    WHERE id_user =' . $_SESSION['user_id'].'
    AND id NOT IN (SELECT id_activite FROM participations WHERE id_participant='.$id_participant.')');
    $stmt->execute();
    $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($activites as $activite) {
        $activites_autorisees[] = $activite['id'];
    }

    if (isset($_GET['id_activite'])) {
        if (valider_id('get', 'id_activite', $bdd, 'activites')) {
            // L'activite est présente et valide donc on poursuit le game et on s'assure que l'activité à laquelle on veut associer le participant n'est pas invalide
            if(in_array($_GET['id_activite'], $activites_autorisees)){
                $id_activite = $_GET['id_activite'];
                // Récupérons le type de l'activité
                $type_activite = $bdd->query('SELECT type_activite FROM activites WHERE id=' . $id_activite);
                $type_activite = $type_activite->fetchAll(PDO::FETCH_ASSOC);
                $type_activite = $type_activite[0]['type_activite'];

                $champs_attendus = ['titre', 'compte_bancaire'];

                if ($type_activite != 1) {
                    $champs_attendus[] = 'nbr_jours';
                    $champs_attendus[] = 'nbr_taches';
                }

                // $type_activite = 2;

                // $stmt = $bdd->prepare('SELECT')
                $redirect = false;

                // On récupère les titres associés à l'activité actuelle
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
            }else{
                $_SESSION['liaison_non_autorisee'] = true;
                header('location:voir_participants.php');
                exit;
            }
        }
    } else {
        $redirect = false;
    }
}

if ($redirect) {
    header('location:voir_participants.php');
    exit;
}

// On entame les validations pour achever la liaison

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
