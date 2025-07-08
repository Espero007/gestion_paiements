<?php

$elements_a_inclure = ['infos_generales', 'infos_bancaires'];
$page_ajout_participant = true;

// Inclusion des entêtes
require_once('entetes.php');

/** Validation des informations du formulaire */

if (isset($_POST['ajouter_participant'])) {

    /** Traitement des informations */

    require_once('validations.php');

    /** Préparatifs pour l'enregistrement des données */

    if (!isset($erreurs)) {
        // S'il n'y a aucune erreur tout va bien je présume
        $matricule_ifu = $_POST['matricule_ifu'];

        // Enregistrement des données textuelles
        // Participants

        $stmt = $bdd->prepare("INSERT INTO participants(id_user, nom, prenoms, matricule_ifu, date_naissance, lieu_naissance, diplome_le_plus_eleve) VALUES (:val1, :val2, :val3, :val4, :val5, :val6, :val7)");

        $stmt->bindParam(':val1', $_SESSION['user_id']);
        $stmt->bindParam(':val2', $_POST['nom']);
        $stmt->bindParam(':val3', $_POST['prenoms']);
        $stmt->bindParam(':val4', $_POST['matricule_ifu']);
        $stmt->bindParam(':val5', $_POST['date_naissance']);
        $stmt->bindParam(':val6', $_POST['lieu_naissance']);
        $stmt->bindParam(':val7', $_POST['diplome_le_plus_eleve']);

        $resultat = $stmt->execute();

        if (!$resultat) {
            // Une erreur s'est produite lors de l'enregistrement des informations
            redirigerVersPageErreur(500, obtenirURLcourant());
        }
        // Le premier enregistrement a été effectué

        // Pour la suite j'ai besoin de l'id du participant donc je le récupère
        $id_participant = $bdd->lastInsertId();

        // Table fichiers

        // 1- Je définis le nom qui va s'appliquer à ce fichier
        // 2- J'enregistre le fichier
        // 3- Je sauvegarde son id
        // 4- J'enregistre en même temps les informations dans la table informations_bancaires

        require_once('enregistrement_fichiers.php');
    }
}

if (isset($traitement_fichiers_ok) && $traitement_fichiers_ok) {
    $_SESSION['participant_ajoute'] = "
         <div>
            <!-- Message proprement dit -->
            <p class=\"m-0\">Le participant a été enregistré avec succès !</p>
            <p class=\"m-0\"><a href=\"ajouter_comptes.php?id_participant=<?php echo $id_participant; ?>\">Cliquez ici</a> si vous souhaitez lui ajouter des comptes bancaires ou ici si vous préférez vous l'<a href=\"/gestion_participants/lier_participant_activite.php?id_participant=<?= $id_participant ?>\">associer</a> directement à une activité</p>
        </div>
    ";
    header('location:gerer_participant.php?id='.$id_participant);
    exit;
}
