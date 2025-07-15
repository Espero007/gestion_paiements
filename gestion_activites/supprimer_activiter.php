<?php
require_once(__DIR__ . '/../includes/bdd.php');

if (isset($_GET['id_activite'])) {
    $idActivite = intval($_GET['id_activite']);

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
        header("Location: /../index.php");
        exit;


    } catch (PDOException $e) {
        $bdd->rollBack();
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }

} else {
    echo "ID de l'activité non fourni.";
}
?>
