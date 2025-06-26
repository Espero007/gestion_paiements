<?php

session_start();
require_once(__DIR__ . '/../../includes/bdd.php');

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
$modification_email = $_GET['modification_email'] ?? '';

// echo $email;
// echo $token;

$stmt = $bdd->prepare('SELECT * FROM connexion WHERE email = :email AND token_verification = :verification');
$stmt->execute([
    "email" => $email,
    "verification" => $token
]);
$utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
$utilisateur = $utilisateur[0];

// var_dump($utilisateur);

if ($utilisateur) {
    // On actualise la base de données
    $stmt = $bdd->prepare("UPDATE connexion SET est_verifie = 1, token_verification = NULL WHERE email = ?");
    $stmt->execute([$email]);

    // On connecte automatiquement l'utilisateur

    $_SESSION['user_id'] = $utilisateur['user_id'];
    $_SESSION['nom'] = $utilisateur['nom'];
    $_SESSION['prenoms'] = $utilisateur['prenoms'];
    $_SESSION['photo_profil'] = $utilisateur['photo_profil'];
    $_SESSION['dernier_signe_activite'] = time();

    header('location:/index.php');
    exit;
}elseif(isset($modification_email) && $modification_email == 1) {

    if (isset($_SESSION['modification_email'])) {
        // L'email veut être modifié
        if ($token == $_SESSION['token'] && $email == $_SESSION['email_a_verifie']) {
            // L'email est validé donc on peut le modifier en bdd
            $stmt = $bdd->prepare("UPDATE connexion SET email = ? WHERE user_id = ?");
            $stmt->execute([$_SESSION['email_a_verifie'], $_SESSION['user_id']]);

            $_SESSION['email_modifie'] = true;
        } else {
            // Il y a un souci avec les infos, sûrement un utilisateur qui veut jouer au malin  
            $_SESSION['lien_invalide'] = true;
        }

        unset($_SESSION['modification_email']);
        unset($_SESSION['token']);
        unset($_SESSION['email_a_verifie']);

        header('location:/parametres/gestion_compte/voir_profil.php');
        exit;
    }else{
        // Il y a eu déconnexion entre temps ou plusieurs liens de confirmation lui ont été envoyés;
        $_SESSION['lien_invalide'] = true;
        header('location:/parametres/gestion_compte/voir_profil.php');
        exit;
    }
}
else {
    header('location:../connexion.php');
    $_SESSION['erreur_avec_verification_email'] = true;
    exit;
}
