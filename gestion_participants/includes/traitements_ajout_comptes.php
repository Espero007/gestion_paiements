<?php

// Tout d'abord on vérifie la présence de l'id du participant qui m'a été envoyée par GET

if (!isset($_GET['id_participant'])) {
    // L'id du participant n'est pas présent
    // redirigerVersPageErreur(404, obtenirURLcourant());
    header('location:voir_participants.php');
    exit;
} else {
    // Nous avons une valeur

    if(valider_id('get', 'id_participant', $bdd)){
        // id valide
        $stmt = "SELECT nom, prenoms, matricule_ifu FROM participants WHERE id_participant=".$_GET['id_participant'];
        $resultat = $bdd->query($stmt);
        if (!$resultat) {
            redirigerVersPageErreur(500, obtenirURLcourant());
        }
        $ligne_recue = $resultat->fetch(PDO::FETCH_NUM);
        $nom = $ligne_recue[0];
        $prenoms = $ligne_recue[1];
        $matricule_ifu = $ligne_recue[2];
        $resultat->closeCursor();
    }else{
        redirigerVersPageErreur(404, obtenirURLcourant());
    }
    

    // On vérifie si c'est bien un nombre
    $id_participant = intval($_GET['id_participant']);
    if ($id_participant == 0) {
        // C'est une chaîne de caractères que j'ai reçue
        redirigerVersPageErreur(404, obtenirURLcourant());
    } else {
        // On vérifie si l'id que j'ai est dans la base de données

        $stmt = "SELECT matricule_ifu FROM participants WHERE id_participant=".$_GET['id_participant'];
        $resultat = $bdd->query($stmt);

        if (!$resultat) {
            // Une erreur s'est produite lors de la récupération
            redirigerVersPageErreur(500, obtenirURLcourant());
        } else {
            // On a pu récupérer la dernière ligne, on checke maintenant si elle correspond à l'id du participant que nous avons chez nous
            if ($resultat->rowCount() == 0) {
                redirigerVersPageErreur(404, obtenirURLcourant());
            }
            $ligne = $resultat->fetch(PDO::FETCH_NUM);
            $matricule_ifu = $ligne[0];
        }
        $resultat->closeCursor();
    }
}

// Nous avons l'id du participant. On vérifie alors si on peut encore lui ajouter des comptes bancaires;

$stmt = $bdd->prepare("SELECT id FROM informations_bancaires WHERE id_participant = :val");
$stmt->bindParam(':val', $id_participant, PDO::PARAM_INT);

if (!$stmt->execute()) {
    redirigerVersPageErreur(500, obtenirURLcourant());
} else {
    $lignes = $stmt->fetchAll(PDO::FETCH_NUM);
    $nombre_comptes_existants = count($lignes);
}

$nombre_comptes_bancaires_permis = NOMBRE_MAXIMAL_COMPTES - $nombre_comptes_existants;

if ($nombre_comptes_bancaires_permis == 0) {
    // On ne peut plus ajouter de comptes pour ce participant
    $quota_comptes_bancaires_atteint = true;
} else {
    // Le quota n'est pas atteint. On peut maintenant s'intéresser à la valeur dans GET['nombre_comptes_bancaires']
    $quota_comptes_bancaires_atteint = false;
    
    if (!isset($_GET['nombre_comptes_bancaires'])) {
        $recuperer_nbr_comptes_bancaires = true;
    } else {
        $recuperer_nbr_comptes_bancaires = false;

        // Tests de validation sur le nombre de comptes bancaires
        // On vérifie si c'est bien un nombre valide
        $valeur = intval($_GET['nombre_comptes_bancaires']);

        if ($valeur < 1 || $valeur > $nombre_comptes_bancaires_permis) {
            // La valeur reçue n'est pas valide
            redirigerVersPageErreur(404, obtenirURLcourant());
        }

        // La valeur est relativement valide
        $elements_a_inclure = ['infos_bancaires'];
        $nombre_comptes_bancaires = $valeur;
        require_once('entetes.php');

        if (isset($_POST['ajouter_comptes'])) {
            // Traitement des fichiers

            require_once('validations.php');

            // On enregistre les données si tout va bien

            if (!isset($erreurs)) {
                // On gère les dossiers d'upload
                $upload_path = creer_dossiers_upload();

                // Mon travail ici va se faire sur les tables "informations bancaires" et "fichiers". Donc on va commencer par enregistrer les fichiers
                require_once('enregistrement_fichiers.php');
            }
        }
    }
}

if(isset($traitement_fichiers_ok) && $traitement_fichiers_ok){
    $_SESSION['comptes_ajoutes'] = true;
    header('location:voir_participants.php');
}
