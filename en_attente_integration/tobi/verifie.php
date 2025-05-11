<?php
require_once(__DIR__."/../../includes/bdd.php");

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

$stmt = $bdd->prepare("SELECT * FROM connexion WHERE email =:email AND token_verification = :verification");
$stmt->execute([
    "email" => $email,
    "verification" => $token]);
$utilisateur = $stmt->fetch();

if ($utilisateur) {
    $stmt = $bdd->prepare("UPDATE connexion SET est_verifie = 1, token_verification = NULL WHERE email = ?");
    $stmt->execute([$email]);
    echo "Compte vérifié avec succès.";
    header("location:index.php");
    exit;
} 

?>