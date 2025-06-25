<?php
session_start();
require_once(__DIR__.'/../includes/bdd.php');
require_once(__DIR__.'/../includes/constantes_utilitaires.php');

$check_data = $bdd->prepare("SELECT email, token_verification FROM connexion WHERE email = :email AND est_verifie=0");
$check_data->bindParam('email', $_GET['email']);
$check_data->execute();

if ($check_data->rowCount() == 1) {
    $data = $check_data->fetchAll(PDO::FETCH_ASSOC);
    $lien_verif = obtenirURLcourant(true) . '/auth/submit/verifie_email.php?email=' . urldecode($_GET['email']) . '&token=' . $data[0]['token_verification'];
    echo($lien_verif);
    envoyerLienValidationEmail($lien_verif, $_GET['email']);

    $_SESSION['email_envoye'] = true;
    header('location:connexion.php');
    exit;
} else {
    header('location:' . $_SESSION['previous_url']);
    exit;
}
