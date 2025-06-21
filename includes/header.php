<?php

session_start();
// session_unset();
require_once('bdd.php');
require_once('constantes_utilitaires.php');

if ($_SESSION['current_url'] != obtenirURLcourant()) {
    // L'url a changé donc on stocke l'url passé
    $_SESSION['previous_url'] = $_SESSION['current_url'];
}

// Récupération de l'adresse courante
$_SESSION['current_url'] = obtenirURLcourant();

// $_SESSION['previous_url'] = obtenirURLcourant();
// $_SESSION['current_url'] = obtenirURLcourant();


if (!(isset($_SESSION['user_id']) && isset($_SESSION['nom']) && isset($_SESSION['prenoms']))) {
    // Utilisateur non connecté

    // On le dirige vers la page de connexion en sauvegardant l'url à laquelle il voulait accéder de base. Ainsi on s'arrange pour qu'il revienne sur cette page une fois qu'il se sera connecté
    $_SESSION['previous_url'] = obtenirURLcourant();
    header('location:/auth/connexion.php');
    exit;
} elseif ((time() - $_SESSION['dernier_signe_activite']) > TIMEOUT) {
    // Le timeout est atteint
    $_SESSION['timeout_atteint'] = true;
    $_SESSION['previous_url'] = obtenirURLcourant();
    header('location:/auth/deconnexion.php');
    exit;
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
            header('location:./auth/connexion.php');
            exit;
        } else {
            // Le gars est bien retrouvé dans la bdd et n'a pas de soucis
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

    <?php if (isset($titre)) : ?>
        <title><?php echo $titre; ?></title>
    <?php else: ?>
        <title>Document</title>
    <?php endif; ?>

    <!-- Custom fonts for this template-->

    <link rel="stylesheet" href="/assets/vendor/fontawesome-free/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">


    <!-- Custom styles for this template-->
     <!-- <link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css"> -->
    <link href="/assets/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom CSS link -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/bootstrap-icons-1.13.1/bootstrap-icons.min.css">

</head>