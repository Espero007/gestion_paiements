<?php
session_start();
require_once(__DIR__ . "/../../includes/bdd.php");

// if(isset($_SESSION['previous_url'])) {
//     echo $_SESSION['previous_url'];
// }

if (isset($_POST['connexion'])) {

    // $champs_attendus = array('email', 'password');

    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        $echec_connexion = true;
    }
    if (empty($_POST['email'])) {
        $erreurs['email'] = "Veuillez remplir ce champ.";
    }
    if (empty($_POST['password'])) {
        $erreurs['password'] = "Veuillez remplir ce champ.";
    }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "L'email que vous avez indiqué n'est pas valide !";
    }
    
    if(!isset($erreurs)) {
        // Tout va bien avec les données car il n'y a pas d'erreurs

        // On vérifie la présence de l'individu dans la base de données
        $check_data = $bdd->prepare("SELECT user_id, nom, prenoms FROM connexion WHERE email = :email AND password = :password");
        $check_data->execute([
            "email" => $_POST["email"],
            "password" => $_POST["password"],
        ]);
        $resultat = $check_data->fetchAll(PDO::FETCH_ASSOC);

        if (count($resultat) == 0) {
            $echec_connexion = true;
        } elseif (count($resultat) == 1) {
            
            // L'individu est présent donc on ajoute ses informations dans notre session
            $logged_user = $resultat[0];
            $_SESSION['user_id'] = $logged_user['user_id'];
            $_SESSION['nom'] = $logged_user['nom'];
            $_SESSION['prenoms'] = $logged_user['prenoms'];

            // Redirection vers la page d'accueil par défaut mais s'il y avait une url on la chope

            if(isset($_SESSION['previous_url'])){
                $url = $_SESSION['previous_url'];
                unset($_SESSION['previous_url']);
                header('location:'.$url);

            }else{
                header('location:../index.php');
            }
        }
    }
}
