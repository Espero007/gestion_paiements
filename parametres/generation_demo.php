<?php
require_once(__DIR__ . '/../includes/header.php');

$stmt = $bdd->query('SELECT * FROM connexion WHERE user_id=' . $_SESSION['user_id'] . ' AND demo=0');
if ($stmt->rowCount() == 1) {
    // L'utilisateur n'a pas encore utiliser l'option de demo donc on configure les éléments en arrière-plan pour le faire
    if (ConfigurerInformationsDemo()) {
        $stmt = $bdd->query('UPDATE connexion SET demo = 1 WHERE user_id=' . $_SESSION['user_id']);
        $_SESSION['infos_demo_ok'] = 'Les informations de démonstration ont été ajoutées et configurées avec succès!';
        header('location:/index.php');
        exit;
    };
} else {
    redirigerVersPageErreur(404);
}
