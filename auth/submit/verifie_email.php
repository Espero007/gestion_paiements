<?php

session_start();
require_once(__DIR__ . '/../../includes/bdd.php');

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
$modification_email = $_GET['modification_email'] ?? '';
$message_lien_invalide = 'Le lien de confirmation que vous avez utilisé est invalide.';

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

    // // On connecte automatiquement l'utilisateur
    // $_SESSION['user_id'] = $utilisateur['user_id'];
    // $_SESSION['nom'] = $utilisateur['nom'];
    // $_SESSION['prenoms'] = $utilisateur['prenoms'];
    // $_SESSION['photo_profil'] = $utilisateur['photo_profil'];
    // $_SESSION['dernier_signe_activite'] = time();

    // // On le redirige vers la page d'accueil
    // header('location:/index.php');
    // exit;

    if (isset($_GET['mdp_oublie'])) {
        if ($_GET['mdp_oublie'] == 1) {
            // L'email est valide mais c'est dans le cadre d'une réinitialisation du mot de passe donc on le redirige vers cette page plutôt que celle de connexion
            $_SESSION['email_confirme'] = 'Votre email a été confirmé avec succès !';
            $_SESSION['email_utilisateur'] = $email;
            header('location:/auth/mdp_oublie.php');
            exit;
        } else {
            redirigerVersPageErreur();
        }
    } else {
        // L'email est validé donc on redirige vers la page de connexion pour qu'il se connecte
        $_SESSION['email_confirme'] = 'Votre email a été confirmé avec succès ! Votre compte est désormais actif, entrez vos identifiants pour vous connecter.';
        header('location:/auth/connexion.php');
        exit;
    }
} elseif (isset($modification_email) && $modification_email == 1) {

    if (isset($_SESSION['modification_email'])) {
        // L'email veut être modifié
        if ($token == $_SESSION['token'] && $email == $_SESSION['email_a_verifie']) {
            // L'email est validé donc on peut le modifier en bdd
            $stmt = $bdd->prepare("UPDATE connexion SET email = ? WHERE user_id = ?");
            $stmt->execute([$_SESSION['email_a_verifie'], $_SESSION['user_id']]);

            $_SESSION['email_modifie'] = true;
        } else {
            // Il y a un souci avec les infos, sûrement un utilisateur qui veut jouer au malin  
            $_SESSION['lien_invalide'] = $message_lien_invalide;
        }

        unset($_SESSION['modification_email']);
        unset($_SESSION['token']);
        unset($_SESSION['email_a_verifie']);

        header('location:/parametres/gestion_compte/voir_profil.php');
        exit;
    } else {
        // Il y a eu déconnexion entre temps ou plusieurs liens de confirmation lui ont été envoyés;
        $_SESSION['lien_invalide'] = $message_lien_invalide;
        header('location:/parametres/gestion_compte/voir_profil.php');
        exit;
    }
} else {
    // L'utilisateur n'a pas été retrouvé et nous ne sommes pas dans une instance de modification d'email. Du coup on va vérifier si tout au moins l'email est valide. Si oui c'est que le token n'est pas bon et de fait on indiquera un message approprié

    $stmt = $bdd->prepare('SELECT * FROM connexion WHERE email = :email');
    $stmt->execute([
        "email" => $email,
    ]);
    if ($stmt->rowCount() != 0) {
        $_SESSION['lien_invalide'] = $message_lien_invalide;
    } else {
        $_SESSION['erreur_avec_verification_email'] = "Une erreur s'est produite lors de la vérification de l'email";
    }
    if (isset($_GET['mdp_oublie'])) {
        if ($_GET['mdp_oublie']) {
            header('location:../mdp_oublie.php');
            exit;
        } else {
            redirigerVersPageErreur();
        }
    } else {
        header('location:../connexion.php');
        exit;
    }
}
