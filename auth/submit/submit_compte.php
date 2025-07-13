<?php
session_start();
require_once(__DIR__.'/../../includes/bdd.php');
require_once(__DIR__.'/../../includes/constantes_utilitaires.php');

if (isset($_POST['inscription'])) {

    $champs_attendus = ['nom', 'prenoms', 'email', 'password'];

    foreach ($champs_attendus as $champ) {
        if (!isset($_POST[$champ])) {
            $echec_connexion = true;
        } elseif (empty($_POST[$champ])) {
            $erreurs[$champ] = "Veuillez remplir ce champ !";
        } elseif ($champ == 'email' && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs[$champ] = "L'email que vous avez indiqué n'est pas valide !";
        } elseif ($champ == 'password') {
            if (strlen($_POST["password"]) < 6 || !preg_match('/^[A-Z]/', $_POST["password"]) || !preg_match('/\d/', $_POST["password"])) {
                $erreurs[$champ] = "Le mot de passe doit contenir au moins 06 caractères; commencer par une lettre majuscule et contenir au moins un chiffre";
            }
        } elseif ($champ == 'email') {
            $check_email = $bdd->prepare("SELECT user_id FROM connexion WHERE email = :email");
            $check_email->execute([
                "email" => $_POST["email"],
            ]);
            $resultat = $check_email->fetchAll(PDO::FETCH_NUM);
            if (count($resultat) != 0) {
                // il y a des lignes donc l'email est déjà dans la bdd
                $erreurs['email'] = "L'email que vous avez indiqué est déjà utilisé !";
            }
        }
    }

    if (!isset($erreurs)) {
        // Pas d'erreurs

        $token = bin2hex(random_bytes(16)); // token de vérification
        $stmt = $bdd->prepare("INSERT INTO connexion(nom,prenoms,email,password,token_verification) VALUES (:val1,:val2,:val3,:val4,:val5)");

        $resultat = $stmt->execute([
            "val1" => $_POST["nom"],
            "val2" => $_POST["prenoms"],
            "val3" => $_POST["email"],
            "val4" => password_hash($_POST["password"], PASSWORD_DEFAULT),
            "val5" => $token
        ]);

        $lien_verif = obtenirURLcourant(true).'/auth/submit/verifie_email.php?email='. urldecode($_POST['email']).'&token='.$token;

        
        if (envoyerLienValidationEmail($lien_verif, $_POST['email'], $_POST['nom'], $_POST['prenoms'], 0)) {
            $_SESSION["email_envoye"] = true;
        }else{
            $_SESSION['email_envoye'] = false;
        }

        if (!$resultat) {
            $echec_enregistrement_donnees = true;
        } else {
            // Insertion réussie
            // $_SESSION['inscription_reussie'] = true;
            header('location:connexion.php');
            exit;
        }
    }
}
