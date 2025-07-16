<?php
session_start();
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');

$check_data = $bdd->prepare("SELECT email, nom, prenoms, token_verification FROM connexion WHERE email = :email AND est_verifie=0");
$check_data->bindParam('email', $_GET['email']);
$check_data->execute();

if ($check_data->rowCount() == 1) {
    $data = $check_data->fetchAll(PDO::FETCH_ASSOC);
    $token = bin2hex(random_bytes((16)));
    $stmt = $bdd->prepare('UPDATE connexion SET token_verification=:token WHERE email=:email');
    $stmt->execute(['token' => $token, 'email' => $_GET['email']]);

    $lien_verif = obtenirURLcourant(true) . '/auth/submit/verifie_email.php?email=' . urldecode($_GET['email']) . '&token=' . $token;

    if (envoyerLienValidationEmail($lien_verif, $_GET['email'], $data[0]['nom'], $data[0]['prenoms'], 0)) {
        $_SESSION["email_envoye"] = 'Un lien de vérification a été renvoyé au mail : <span class="text-primary">' . htmlspecialchars($_GET['email']) . '</span>. Cliquez dessus pour confirmer votre email et accéder à votre compte.';
    } else {
        $_SESSION['email_non_envoye'] = 'Une erreur d\'est produite lors du renvoi du lien de confirmation de votre email, veuillez réessayer plus tard';
    }

    header('location:connexion.php');
    exit;
} else {
    header('location:' . $_SESSION['previous_url']);
    exit;
}
