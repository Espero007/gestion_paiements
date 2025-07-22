<?php
session_start();
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');

// Je vais recevoir l'id du participant par une get request. S'il n'est pas présent ou s'il est invalide je renvoie vers la page précédente.
// Après la suppression je vais rediriger vers la page d'ajout de participant

/*

    Supprimer un participant revient à la shout de la table participants, de la table informations bancaires, de la table fichiers et de la table participations si le participant est déjà là bas

*/

if (!valider_id('get', 'id', $bdd, 'participants')) {
    redirigerVersPageErreur();
} else {
    // L'id est présent et valide
    $id_participant = dechiffrer($_GET['id']);

    // Je supprime dans la table 'participants'
    $stmt = "DELETE FROM participants WHERE id_participant=$id_participant";
    $bdd->exec($stmt);

    // Je récupère les ribs qui lui appartiennent
    $stmt = $bdd->query("SELECT id_rib FROM informations_bancaires WHERE id_participant=$id_participant");
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resultats as $index => $rib) {
        $ribs[] = $rib['id_rib'];
    }

    // Je supprime dans la table 'information bancaires
    $stmt = "DELETE FROM informations_bancaires WHERE id_participant=$id_participant";
    $bdd->exec($stmt);

    // Je supprime les fichiers et les informations dans la table fichiers

    foreach ($ribs as $rib) {
        // Je récupère le chemin d'accès au fichier que nous autres avons sauvegardé
        $stmt = $bdd->query('SELECT chemin_acces FROM fichiers WHERE id_fichier=' . $rib);
        $chemin = $stmt->fetchAll(PDO::FETCH_NUM);
        $chemin = $chemin[0][0];
        unlink($chemin); // Suppresion du fichier

        // Suppression de la ligne dans la table
        $stmt = "DELETE FROM fichiers WHERE id_fichier=$rib";
        $bdd->exec($stmt);
    }

    // Je supprime les lignes dans la table participations
    $stmt = "DELETE FROM participations WHERE id_participant=$id_participant";
    $bdd->exec($stmt);

    // Je redirige vers la page des participants avec le message de succès
    $_SESSION['suppression_ok'] = true;
    header('location:voir_participants.php');
    exit;
}
