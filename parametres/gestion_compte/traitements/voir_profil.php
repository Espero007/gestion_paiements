<?php

// Récupération des informations de l'utilisateur
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

if (isset($_POST['choisir_photo'])) {
    $fichier = 'photo';
    // L'utilisateur veut changer sa photo de profil

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
        $stmt->closeCursor();
        if ($chemin && file_exists($chemin)) {
            unlink($chemin);
        }

        if (move_uploaded_file($infos_fichier['tmp_name'], $chemin_absolu)) {
            $stmt = $bdd->prepare('UPDATE connexion SET photo_profil=:photo WHERE user_id=' . $_SESSION['user_id']);
            $stmt->execute(['photo' => $chemin_absolu]);
            $_SESSION['photo_profil'] = $nom_fichier;
            $photo_modifie = true;
            $_POST = [];
        }
    }
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

            $infos_modifiees = true;
        }

        // Il faut que j'envoie un mail de confirmation à son email avant de l'actualiser. Donc comment je fais ça ?
        // Je vais sauvegarder l'email dans la session ainsi que le token qui sera généré puis sur la page de vérification je vais me servir de ces informations, vérifier l'email et peut être mettre un timer pour que le lien expire

        if ($_POST['email'] != $utilisateur['email']) {
            // $modifier_email = true;

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
            $_POST = [];
        }
    }
}
