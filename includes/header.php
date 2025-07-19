<?php

session_start();
require_once('bdd.php');
require_once('constantes_utilitaires.php');

if ($_SESSION['current_url'] != obtenirURLcourant()) {
    // L'url a changé donc on stocke l'url passé
    $_SESSION['previous_url'] = $_SESSION['current_url'];
}

// Récupération de l'adresse courante
$_SESSION['current_url'] = obtenirURLcourant();

if (!(isset($_SESSION['user_id']) && isset($_SESSION['nom']) && isset($_SESSION['prenoms']))) {
    // Utilisateur non connecté
    // On le dirige vers la page de connexion en sauvegardant l'url à laquelle il voulait accéder de base. Ainsi on s'arrange pour qu'il revienne sur cette page une fois qu'il se sera connecté
    $_SESSION['previous_url'] = obtenirURLcourant();
    //header('location:/auth/connexion.php');
    //exit;
} elseif ((time() - $_SESSION['dernier_signe_activite']) > TIMEOUT) {
    // Le timeout est atteint mais il ne faut pas le déconnecter s'il a un cookie actif donc il faut s'assurer qu'il n'a pas de cookie

    $stmt = $bdd->query('SELECT * FROM token_souvenir WHERE user_id=' . $_SESSION['user_id']);
    if ($stmt->rowCount() == 0 && !isset($_COOKIE['souvenir'])) {
        // Il n'a pas de cookie ni dans la superglobale ni dans la bdd
        $_SESSION['timeout_atteint'] = true;
        $_SESSION['previous_url'] = obtenirURLcourant();
        header('location:/auth/deconnexion.php');
        exit;
    }else{
        // Il a un cookie actif donc on remet le compteur à 0
        $_SESSION['dernier_signe_activite'] = time();
    }
} else {

    // On vérifie la présence de l'individu dans la base de données
    $stmt = $bdd->prepare("SELECT user_id FROM connexion WHERE user_id = :user_id AND nom = :nom AND prenoms = :prenoms");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':nom', $_SESSION['nom']);
    $stmt->bindParam(':prenoms', $_SESSION['prenoms']);

    if (!$stmt->execute()) {
        // La récupération de l'information en base de données a rencontré un problème donc on va rediriger vers la page d'erreur avec comme erreur 500
        redirigerVersPageErreur(500, $current_url);
    } else {
        // La récupération n'a pas eu de problèmes
        $lignes = $stmt->fetchAll(PDO::FETCH_NUM);
        if (empty($lignes)) {
            // Il y a un souci, l'utilisateur n'a pas été retrouvé en bdd donc on redirige vers la page de connexion sans préavis en supprimant la session en cours
            session_unset();
            session_destroy();
            header('location:/auth/connexion.php');
            exit;
        } else {

            // Le gars est bien retrouvé dans la bdd
            // On vérifie que son cookie (s'il en a) n'est pas encore arrivé à expiration : bh en fait quand bien même il aurait un cookie, s'il arrive à expiration, il est supprimé automatiquement donc poser des conditions sur la présence du cookie après expiration n'est pas une bonne idée.

            $stmt = $bdd->query('SELECT * FROM token_souvenir WHERE user_id=' . $_SESSION['user_id']);
            if ($stmt->rowCount() != 0 && !isset($_COOKIE['souvenir'])) {
                // Il avait un cookie mais le cookie en question est arrivé à expiration donc on le déconnecte automatiquement
                $_SESSION['cookie_expire'] = 'Veuillez vous connecter à nouveau.';
                header('location:/auth/deconnexion.php');
                exit;
            }

            $_SESSION['dernier_signe_activite'] = time();
            $_SESSION['current_url'] = obtenirURLcourant();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <?php if (isset($titre_page)) : ?>
        <title><?php echo $titre_page; ?></title>
    <?php else: ?>
        <title>Document</title>
    <?php endif; ?>

    <!-- Custom fonts for this template-->

    <link rel="stylesheet" href="/assets/vendor/fontawesome-free/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">


    <!-- Custom styles for this template-->
    <link href="/assets/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom CSS link -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/bootstrap-icons-1.13.1/bootstrap-icons.min.css">

    <!-- Custom styles for this page -->
    <link href="/assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- Style loader -->
     <link rel="stylesheet" href="/includes/loader.css">

</head>