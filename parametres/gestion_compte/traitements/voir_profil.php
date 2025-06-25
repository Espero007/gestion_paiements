<?php

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
        $upload_path = BASE_PATH.'/photos_profil/';
        // On modifie le nom du fichier
        $nom_fichier = $fichier . '_profil_' . 'user_' . $_SESSION['user_id'] .'.'. strtolower(pathinfo($infos_fichier['name'], PATHINFO_EXTENSION));
        $chemin_absolu = $upload_path . $nom_fichier;
        // echo $chemin_absolu;

        if (move_uploaded_file($infos_fichier['tmp_name'], $chemin_absolu)) {
            $stmt = $bdd->query('UPDATE connexion SET photo_profil=\'' . $nom_fichier . '\' WHERE user_id=' . $_SESSION['user_id']);
            $_SESSION['photo_profil'] = $nom_fichier;
            $photo_modifie = true;
        }
    }
}
