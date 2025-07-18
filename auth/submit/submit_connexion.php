<?php
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

$_SESSION['current_url'] = obtenirURLcourant();

// Redirection vers la page d'accueil si l'utilisateur est déjà connecté

if (isset($_SESSION['user_id']) && !isset($_SESSION['deconnexion'])) {
    // L'utilisateur est connecté
    header('location:/index.php');
    exit;
}

if (isset($_POST['connexion'])) {

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

    if (!isset($erreurs)) {
        // Tout va bien avec les données : pas d'erreurs

        // On vérifie d'abord si les informations de l'utilisateur ne correspondent pas à ceux d'un utilisateur qui n'a pas encore confirmé son email
        $check_data = $bdd->prepare("SELECT email, token_verification FROM connexion WHERE email = :email AND est_verifie=0");
        $check_data->bindParam('email', $_POST['email']);
        $check_data->execute();

        if ($check_data->rowCount() == 1) {
            // L'utilisateur n'a pas encore validé son email
            $email_non_valide = true;
        } else {
            // L'email indiqué n'est soit pas dans la bdd soit il est déjà validé
            // On vérifie la présence de l'individu dans la base de données
            $check_data = $bdd->prepare("SELECT user_id, nom, prenoms, photo_profil, password FROM connexion WHERE email = :email AND est_verifie= 1");
            $check_data->execute([
                "email" => $_POST["email"],
            ]);
            $resultat = $check_data->fetchAll(PDO::FETCH_ASSOC);

            if (count($resultat) == 1 && password_verify($_POST['password'], $resultat[0]['password'])) {
                // L'individu est présent donc on ajoute ses informations dans notre session
                $logged_user = $resultat[0];
                $_SESSION['user_id'] = $logged_user['user_id'];
                $_SESSION['nom'] = $logged_user['nom'];
                $_SESSION['prenoms'] = $logged_user['prenoms'];
                $_SESSION['photo_profil'] = $logged_user['photo_profil'];
                $_SESSION['dernier_signe_activite'] = time();

                if (isset($_POST['souvenir'])) {
                    $token = bin2hex(random_bytes(16));
                    $nbr_jours = 15;
                    $expire = date('Y-m-d H:i:s', time() + (86400 * $nbr_jours)); // expire au bout de $nbr_jours jours

                    $smt = $bdd->prepare("INSERT INTO token_souvenir(user_id,token, expire_le) VALUES (?,?,?)");
                    $smt->execute([$logged_user['user_id'], hash('sha256', $token), $expire]);
                    setcookie('souvenir', $token, time() + (86400 * $nbr_jours), '/', '', true, true); // expire au bout 30 jours
                }

                // Redirection vers la page d'accueil par défaut mais s'il y avait une url on la chope
                if (isset($_SESSION['previous_url'])) {
                    header('location:' . $_SESSION['previous_url']);
                    exit;
                } else {
                    header('location:/index.php');
                    exit;
                }
            } else {
                $echec_connexion = true;
            }
        }
    }
}
