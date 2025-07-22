<?php

require_once(__DIR__ . '/../includes/header.php');

$stmt = $bdd->query('SELECT * FROM connexion WHERE user_id=' . $_SESSION['user_id'] . ' AND demo=1');
if ($stmt->rowCount() == 1) {
    // L'utilisateur a déjà généré des informations de démo et voudrait les retirer
    unlink(__DIR__ . '/donnees.csv');
    // Le travail se fera avec les tables activites, participants, fichiers et informations_demo

    // 1ère étape : Récupérer les ids des activités et les supprimer

    $stmt = $bdd->query('SELECT ids_activites FROM informations_demo WHERE id_user=' . $_SESSION['user_id']);
    $ids_activites = $stmt->fetch(PDO::FETCH_NUM);
    $ids_activites = $ids_activites[0];
    $ids_activites = explode(',', $ids_activites);
    foreach ($ids_activites as $id) {
        $stmt = $bdd->query('DELETE FROM activites WHERE id=' . $id);
    }

    $stmt = $bdd->query('SELECT ids_participants FROM informations_demo WHERE id_user=' . $_SESSION['user_id']);
    $ids_acteurs = $stmt->fetch(PDO::FETCH_NUM);
    $ids_acteurs = $ids_acteurs[0];
    $ids_acteurs = explode(',', $ids_acteurs);

    foreach ($ids_acteurs as $id) {
        // 2ème étape : Supprimer les fichiers
        // D'abord on récupère les chemins des fichiers rib pour les supprimer

        $stmt = $bdd->query('
        SELECT chemin_acces
        FROM fichiers
        WHERE id_fichier IN 
        (SELECT id_fichier
        FROM fichiers f
        INNER JOIN informations_bancaires ib ON ib.id_rib = f.id_fichier
        WHERE ib.id_participant=' . $id . '
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
            WHERE ib.id_participant = ' . $id . '
        )');

        // 3ème étape : Table participants
        $stmt = $bdd->query('DELETE FROM participants WHERE id_participant=' . $id);
    }

    // 4ème étape : Table informations_demo
    $stmt = $bdd->query('DELETE FROM informations_demo WHERE id_user=' . $_SESSION['user_id']);

    // 5ème étape : Ramener 'demo' à '0'
    $stmt = $bdd->query('UPDATE connexion SET demo = 0 WHERE user_id=' . $_SESSION['user_id']);

    $_SESSION['infos_demo_ok'] = 'Les informations de démonstration ont été supprimées avec succès!';
    header('location:/index.php');
    exit;
} else {
    redirigerVersPageErreur();
}
