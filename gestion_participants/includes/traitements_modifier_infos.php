<?php
// Validation id

if (!isset($_GET['id_participant'])) {
    redirigerVersPageErreur(404, $current_url);
} else {
    if (valider_id_participant($_GET['id_participant'], $bdd, obtenirURLcourant())) {
        // L'id est valide. Je vais prendre les informations du participant
        $id_participant = $_GET['id_participant'];

        $stmt = "SELECT * FROM participants WHERE id_participant=" . $id_participant;
        $resultat = $bdd->query($stmt);

        if (!$resultat) {
            redirigerVersPageErreur(500, obtenirURLcourant());
        }
        $infos_participant = $resultat->fetch(PDO::FETCH_ASSOC);
        $resultat->closeCursor();
    } else {
        redirigerVersPageErreur(404, obtenirURLcourant());
    }
}

$elements_a_inclure = ['infos_generales', 'infos_bancaires'];
$page_modification = true;

// Inclusion des informations générales et/ou bancaires
require_once('entetes.php');

/** Validation des informations reçues */

if (isset($_POST['modifier_infos'])) {
    // Traitement des informations textuelles et/ou bancaires
    require_once('validations.php');

    // Mise à jour des données

    if (!isset($erreurs)) {

        // Table Participants

        $stmt = $bdd->prepare('UPDATE participants SET nom=:val1, prenoms=:val2, matricule_ifu=:val3, date_naissance=:val4, lieu_naissance=:val5 WHERE id_participant=' . $id_participant);

        $stmt->bindParam(':val1', $_POST['nom']);
        $stmt->bindParam(':val2', $_POST['prenoms']);
        $stmt->bindParam(':val3', $_POST['matricule_ifu']);
        $stmt->bindParam(':val4', $_POST['date_naissance']);
        $stmt->bindParam(':val5', $_POST['lieu_naissance']);

        $resultat = $stmt->execute();

        if (!$resultat) {
            redirigerVersPageErreur(500, $current_url);
        }

        // On passe aux informations bancaires
        require_once('enregistrement_fichiers.php');
    }
}

if (isset($traitement_fichiers_ok) && $traitement_fichiers_ok) {
    $message_succes = true;
}
