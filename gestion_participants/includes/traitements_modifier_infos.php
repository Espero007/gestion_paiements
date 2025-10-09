<?php
// Validation id

if (valider_id('get', 'id', $bdd)) {
    // L'id est valide. Je vais prendre les informations du participant
    $id_participant = dechiffrer($_GET['id']);

    $stmt = "SELECT * FROM participants WHERE id_participant=" . $id_participant;
    $resultat = $bdd->query($stmt);

    if (!$resultat) {
        redirigerVersPageErreur(500, obtenirURLcourant());
    }
    $infos_participant = $resultat->fetch(PDO::FETCH_ASSOC);
    $resultat->closeCursor();
} else {
    redirigerVersPageErreur();
}

$elements_a_inclure = ['infos_generales'];

// Ici, nous indiquons les éléments à inclure et à afficher sur la page de modification, essentiellement les informations générales et les informations bancaires. Mais étant donné qu'il est désormais possible pour un acteur de ne pas avoir d'informations bancaires, on ne va pas inclure d'office les informations bancaires. On va tout d'abord vérifier que l'individu ait des informations bancaires. Si oui on les affiche pour modification, sinon on skippe

$comptes = $bdd->query("SELECT * FROM informations_bancaires WHERE id_participant=" . $id_participant);
$comptes = $comptes->fetch(PDO::FETCH_ASSOC);
if($comptes){
    // On a retrouvé des comptes pour l'acteur donc on peut afficher les informations bancaires
    $elements_a_inclure[] = 'infos_bancaires';
}

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

        $stmt = $bdd->prepare('UPDATE participants SET nom=:val1, prenoms=:val2, matricule_ifu=:val3, date_naissance=:val4, lieu_naissance=:val5, diplome_le_plus_eleve = :val6, reference_carte_identite = :val7 WHERE id_participant=' . $id_participant);

        $stmt->bindParam(':val1', $_POST['nom']);
        $stmt->bindParam(':val2', $_POST['prenoms']);
        $stmt->bindParam(':val3', $_POST['matricule_ifu']);
        $stmt->bindParam(':val4', $_POST['date_naissance']);
        $stmt->bindParam(':val5', $_POST['lieu_naissance']);
        $stmt->bindParam(':val6', $_POST['diplome_le_plus_eleve']);
        $stmt->bindParam(':val7', $_POST['reference_carte_identite']);

        $resultat = $stmt->execute();

        if (!$resultat) {
            redirigerVersPageErreur(500, $current_url);
        }

        if(in_array('infos_bancaires', $elements_a_inclure)){
            // On passe aux informations bancaires
            require_once('enregistrement_fichiers.php');
        }else{
            $traitements_ok = true;
        }
    }
}

if ((isset($traitement_fichiers_ok) && $traitement_fichiers_ok) || (isset($traitements_ok) && $traitements_ok)) {
    if(isset($page_modification) && !$pas_de_modifications) $_SESSION['modification_ok'] = 'Les informations de l\'acteur ont été modifiées avec succès';
    header('location:gerer_participant.php?id=' . chiffrer($id_participant));
    exit;
}
