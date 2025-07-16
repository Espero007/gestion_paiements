<?php

require_once(__DIR__ . '/../../../includes/bdd.php');

$confirmation = true;
if (isset($_POST['desactiver'])) {
    if (!isset($_POST['suppressionCompte'])) {
        $confirmation = false;
    } else {
        $stmt = $bdd->query('SELECT * FROM connexion WHERE user_id=' . $_SESSION['user_id']);
        $utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $utilisateur = $utilisateur[0];

        if ($utilisateur) {
            $smt = $bdd->prepare("DELETE FROM connexion WHERE user_id =?");
            $smt->execute([$_SESSION['user_id']]);

            header('Location: ../../../index.php');
            exit();
        }
    }
}
