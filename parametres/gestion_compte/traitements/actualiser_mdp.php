<?php
session_start();
require_once(__DIR__ . '/../../../includes/bdd.php');

$smt = $bdd->query('SELECT * FROM connexion WHERE user_id=' . $_SESSION['user_id']);
$mot_de_passe = $smt->fetch(PDO::FETCH_ASSOC);

$error = [];

if (isset($_POST['modifier_mdp'])) {
    $mdp_actuel = $_POST['mdp_actuel'];
    $nouveau_mdp = $_POST['nouveau_mdp'];

    if (!isset($mdp_actuel) || empty($mdp_actuel)) {
        $error['mdp_actuel'] = "Ce champ est requis";
    }
    if (!isset($nouveau_mdp) || empty($nouveau_mdp)) {
        $error['nouveau_mdp'] = "Ce champ est requis";
    }
    if (empty($error)) {
        if ($mot_de_passe && password_verify($mdp_actuel, $mot_de_passe['password'])) {
            if (strlen($_POST["nouveau_mdp"]) < 6 || !preg_match('/^[A-Z]/', $_POST["nouveau_mdp"]) || !preg_match('/\d/', $_POST["nouveau_mdp"])) {
                $error["nouveau_mdp"] = "Le mot de passe doit contenir au moins 06 caractères ; commencer par une lettre majuscule et contenir au moins un chiffre";
            } else {
                $stmt = $bdd->prepare('UPDATE connexion SET password = ? WHERE user_id = ?');
                $stmt->execute([password_hash($nouveau_mdp, PASSWORD_DEFAULT), $_SESSION['user_id']]);
                $_SESSION['mdp_actualise'] = 'Mot de passe actualisé avec succès';
            }
        } else {
            $error['mdp_actuel'] = "Mot de passe incorrect";
        }
    }

    $_SESSION['erreurs'] = $error;
    header('location:../voir_profil.php');
}
