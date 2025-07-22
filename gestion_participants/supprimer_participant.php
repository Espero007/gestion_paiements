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

    // On supprime d'abord les fichiers

    $stmt = $bdd->query('
        SELECT chemin_acces
        FROM fichiers
        WHERE id_fichier IN 
        (SELECT id_fichier
        FROM fichiers f
        INNER JOIN informations_bancaires ib ON ib.id_rib = f.id_fichier
        WHERE ib.id_participant=' . $id_participant . '
        )');
    $chemins = $stmt->fetchAll(PDO::FETCH_NUM);
    foreach ($chemins as $chemin) {
        $path = $chemin[0];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    // Ensuite on supprime les lignes dans la table fichiers
    $stmt = $bdd->query('
        DELETE FROM fichiers
        WHERE id_fichier IN
        (
            SELECT id_fichier
            FROM fichiers f
            INNER JOIN informations_bancaires ib ON ib.id_rib = f.id_fichier
            WHERE ib.id_participant = ' . $id_participant . '
        )');


    // Je supprime dans la table 'participants'. Par effet de cascade, les ids correspondant seront supprimés dans les tables informations_bancaires et participations
    $stmt = $bdd->query("DELETE FROM participants WHERE id_participant=$id_participant");

    // Je redirige vers la page des participants avec le message de succès
    $_SESSION['suppression_acteur_ok'] = true;
    header('location:voir_participants.php');
    exit;
}
