<?php
session_start();
require_once 'includes/bdd.php'; // adapte le chemin si nécessaire

// 1. Si l'utilisateur n'est pas connecté mais qu'un cookie "souvenir" est présent
if (!isset($_SESSION['user_id']) && isset($_COOKIE['souvenir'])) {

    // Affiche pour débogage
    echo "Cookie détecté : " . $_COOKIE['souvenir'] . "<br>";

    $token = hash('sha256', $_COOKIE['souvenir']);

    $stmt = $bdd->prepare("SELECT user_id FROM token_souvenir WHERE token = ? AND expire_le > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        echo "Utilisateur reconnu, restauration de session.<br>";
        $_SESSION['user_id'] = $user['user_id'];
    } else {
        echo "Token invalide ou expiré<br>";
        setcookie('souvenir', '', time() - 3600, '/');
    }
}

// 2. Si l'utilisateur n'est toujours pas connecté, on redirige
if (!isset($_SESSION['user_id'])) {
    echo "Redirection vers connexion<br>";
    exit(header("Location: auth/connexion.php"));
}
