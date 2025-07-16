<?php
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../constantes_utilitaires.php');

if (valider_id('get', 'id', '', 'activites')) {
    $idActivite = $_GET['id_activite'];

    try {
        $bdd->beginTransaction();

        // Suppression des dépendances
        $bdd->prepare("DELETE FROM participations WHERE id_activite = ?")->execute([$idActivite]);
        $bdd->prepare("DELETE FROM diplomes WHERE id_activite = ?")->execute([$idActivite]);
        $bdd->prepare("DELETE FROM titres WHERE id_activite = ?")->execute([$idActivite]);

        // Suppression de l'activité
        $bdd->prepare("DELETE FROM activites WHERE id = ?")->execute([$idActivite]);

        $bdd->commit();

        // Suppression réussie
        $_SESSION['suppression_ok'] = 'L\'activité a été supprimée avec succès';
        header("Location:/gestion_activites/voir_activites.php");
        exit;
    } catch (PDOException $e) {
        $bdd->rollBack();
        // echo "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    header('location:' . $_SESSION['previous_url']);
    exit;
}
