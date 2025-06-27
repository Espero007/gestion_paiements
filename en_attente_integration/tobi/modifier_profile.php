<?php

require_once(__DIR__ . '/../../includes/bdd.php');

$user_id = $_SESSION['user_id'];


if (isset($_POST['enregistrer'])) {

    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (isset($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur_mail = "Entrer un email valide";
    }

    if (!empty($password)) {
        if (strlen($password) < 6 || !preg_match('/^[A-Z]/', $password) || !preg_match('/\d/', $password)) {
            $erreur = "Le mot de passe doit contenir au moins 06 caractères; commencer par une lettre majuscule et contenir au moins un chiffre";
        } else {

            $stmt = $bdd->prepare("UPDATE connexion SET nom = ?, prenoms = ?, email = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT), $user_id]);
        }
    } else {
        $stmt = $bdd->prepare("UPDATE connexion SET nom = ?, prenoms = ?, email = ? WHERE user_id = ?");
        $stmt->execute([$nom, $prenom, $email, $user_id]);
    }

    $info = 'Profile mis à jour avec succès!';
}
