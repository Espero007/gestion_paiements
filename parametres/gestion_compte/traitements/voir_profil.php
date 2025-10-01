<?php
session_start();
require_once(__DIR__ . '/../../../includes/bdd.php');
require_once(__DIR__ . '/../../../includes/constantes_utilitaires.php');

$stmt = $bdd->query('SELECT nom, prenoms, email, photo_profil FROM connexion WHERE user_id=' . $_SESSION['user_id']);
$utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
$utilisateur = $utilisateur[0];

$taille_image = 2e6; // 2Mo
$extensions_autorisees = ['jpg', 'jpeg', 'png'];
$erreursUploadFichier = array(
    0 => "Il n\'y a pas d'erreur, le téléversement s'est déroulé avec succès.",
    1 => "La taille du fichier sélectionné excède la taille maximale prévue dans le fichier php.ini.",
    2 => "La taille du fichier excède la taille maximale prévue : " . $taille_image / 1e6 . " Mo.",
    3 => "Le fichier sélectionné a seulement été partiellement téléversé.",
    4 => "Aucun fichier sélectionné",
    6 => "Un dossier temporaire manquant.",
    7 => "Impossible d'écrire sur le disque dur.",
    8 => "Une extension PHP a empêché le téléversement du fichier"
);

if (isset($_POST['changer_photo'])) {
    // L'utilisateur veut changer sa photo de profil
    $fichier = 'photo';
    // Validations
    if (!array_key_exists($fichier, $_FILES)) {
        $erreurs[$fichier][] = 'Une erreur s\'est produite';
    } else {
        // L'image est bien présente
        $infos_fichier = $_FILES[$fichier];
        if ($infos_fichier['error'] != 0) {
            // On vérifie les erreurs possibles
            $type_erreur = $infos_fichier['error'];
            $erreurs[$fichier][] = $erreursUploadFichier[$type_erreur];
        } elseif ($infos_fichier['size'] > $taille_image) {
            // La taille du fichier n'est pas celle permise
            $erreurs[$fichier][] = $erreursUploadFichier[2];
        } else {
            // On vérifie l'extension du fichier
            $extension_upload = strtolower(pathinfo($infos_fichier['name'], PATHINFO_EXTENSION));

            if (!in_array($extension_upload, $extensions_autorisees)) {
                // Le fichier n'a pas la bonne extension
                $erreurs[$nom_fichier][] = "Le fichier attendu est de type PDF";
            }
        }
    }

    if (isset($erreurs)) {
        $_SESSION['erreurs'] = $erreurs;
    }

    // Mise à jour de la photo de profil

    if (!isset($erreurs)) {
        // $upload_path = creer_dossiers_upload();
        $upload_path = BASE_PATH . '/photos_profil/';
        // On modifie le nom du fichier
        $nom_fichier = $fichier . '_profil_' . chiffrer($_SESSION['user_id']) . '.' . strtolower(pathinfo($infos_fichier['name'], PATHINFO_EXTENSION));
        $chemin_absolu = $upload_path . $nom_fichier;
        // echo $chemin_absolu;

        // N'oublions pas de supprimer le fichier associé à son ancienne photo de profil s'il en avait un
        $stmt = $bdd->query('SELECT photo_profil FROM connexion WHERE user_id=' . $_SESSION['user_id']);
        $chemin = $stmt->fetch(PDO::FETCH_NUM)[0];
        $chemin = $upload_path . $chemin;
        $stmt->closeCursor();
        if ($chemin && file_exists($chemin)) {
            unlink($chemin);
        }

        if (move_uploaded_file($infos_fichier['tmp_name'], $chemin_absolu)) {
            $stmt = $bdd->prepare('UPDATE connexion SET photo_profil=:photo WHERE user_id=' . $_SESSION['user_id']);
            $stmt->execute(['photo' => $nom_fichier]);
            $_SESSION['photo_profil'] = $nom_fichier;
            $_SESSION['photo_modifiee'] = 'Votre photo a été modifiée avec succès !';
            // unset($_POST['changer_photo']);
            // header('location:' . $_SERVER['PHP_SELF']); // On redirige vers la même page pour réactualiser la page. Cela a été fait pour qu'une fois la photo changée, le formulaire ne soit pas rémanent
        }
    }
    // On redirige vers la page de base
    header('location:../voir_profil.php');
}

if (isset($_POST['modifier_infos'])) {
    // Appliquer les validations aux données reçues puis les actualiser en bdd
    $champs_attendus = ['nom', 'prenoms', 'email'];

    foreach ($champs_attendus as $champ) {
        if (!isset($_POST[$champ])) {
            // La valeur n'est pas dans le champ
            $erreurs[$champ][] = 'Un erreur s\'est produite';
        } else {
            // La valeur est bien présente adns la superglobale
            $valeur_champ = $_POST[$champ];

            if ($champ == 'nom' || $champ == 'prenoms') {
                if (preg_match('/[^\p{L} -]/u', $valeur_champ)) {
                    $erreurs[$champ][] = "Ce champ doit être une chaîne de caractères alphabétiques !";
                } elseif (strlen($valeur_champ) > 100) {
                    $erreurs[$champ][] = "La valeur de ce champ ne doit pas excéder 100 caractères";
                }
            } elseif ($champ == 'email') {
                if (!filter_var($valeur_champ, FILTER_VALIDATE_EMAIL)) {
                    // L'email est invalide
                    $erreurs[$champ][] = 'L\'email que vous avez indiqué est invalide';
                }
            }
        }
    }

    if (!isset($erreurs)) {
        // On modifie les informations dans la session pour qu'il n'ait pas à se reconnecter et on les modifie ensuite dans la bdd
        if ($_POST['nom'] != $_SESSION['nom'] || $_POST['prenoms'] != $_SESSION['prenoms']) {
            $_SESSION['nom'] = $_POST['nom'];
            $_SESSION['prenoms'] = $_POST['prenoms'];

            $utilisateur['nom'] = $_POST['nom'];
            $utilisateur['prenoms'] = $_POST['prenoms'];

            $stmt = $bdd->prepare("UPDATE connexion SET nom = ?, prenoms = ? WHERE user_id = ?");
            $stmt->execute([$_POST['nom'], $_POST['prenoms'], $_SESSION['user_id']]);
            $_SESSION['infos_modifiees'] = 'Vos informations ont été modifiées avec succès !';
            // $infos_modifiees = true;
        }

        // Il faut que j'envoie un mail de confirmation à son email avant de l'actualiser. Donc comment je fais ça ?
        // Je vais sauvegarder l'email dans la session ainsi que le token qui sera généré puis sur la page de vérification je vais me servir de ces informations, vérifier l'email et peut être mettre un timer pour que le lien expire

        if ($_POST['email'] != $utilisateur['email']) {
            // On vérifie aussi si l'email indiqué n'est pas déjà utilisé par un autre utilisateur car c'est important, un email ne doit identifier qu'un seul utilisateur, même si ce même utilisateur a deux mails

            $stmt = $bdd->prepare('SELECT email FROM connexion WHERE email = :email');
            $stmt->execute(['email' => $_POST['email']]);
            $resultat = $stmt->fetchAll(PDO::FETCH_NUM);

            if(count($resultat) == 0){
                // L'email indiqué est safe

                $token = bin2hex(random_bytes(16));
                $lien_verif = $lien_verif = obtenirURLcourant(true) . '/auth/submit/verifie_email.php?email=' . urldecode($_POST['email']) . '&token=' . $token . '&modification_email=1';

                if (envoyerLienValidationEmail($lien_verif, $_POST['email'], $_SESSION['nom'], $_SESSION['prenoms'], 1)) {
                    $_SESSION['modification_email'] = true;
                    $_SESSION['email_a_verifie'] = $_POST['email'];
                    $_SESSION['token'] = $token;
                    $email_envoye = true;
                } else {
                    $email_envoye = false;
                }
                $_SESSION['email_envoye'] = $email_envoye;
            }else{
                // L'email indiqué est déjà associé à un autre utilisateur de la plateforme
                $_SESSION['email_deja_pris'] = 'L\'email que vous avez indiqué semble déjà avoir été associé à un autre utilisateur de la plateforme. Utilisez-en un autre.';
            }
            // $modifier_email = true;
        }
    } else {
        $_SESSION['erreurs'] = $erreurs;
    }

    header('location:../voir_profil.php');
}
