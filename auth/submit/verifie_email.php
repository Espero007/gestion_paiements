<?php

session_start();
require_once(__DIR__.'/../../includes/bdd.php');

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

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

if($utilisateur){
    // On actualise la base de données
    $stmt = $bdd->prepare("UPDATE connexion SET est_verifie = 1, token_verification = NULL WHERE email = ?");
    $stmt->execute([$email]);

    // On connecte automatiquement l'utilisateur

    $_SESSION['user_id'] = $utilisateur['user_id'];
    $_SESSION['nom'] = $utilisateur['nom'];
    $_SESSION['prenoms'] = $utilisateur['prenoms'];
    $_SESSION['dernier_signe_activite'] = time();
    header('location:/index.php');
    exit;
}else{
    // header('location:../connexion.php');
    // exit;
}