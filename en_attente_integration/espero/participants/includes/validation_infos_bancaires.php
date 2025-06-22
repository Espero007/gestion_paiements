<?php

if(isset($page_modification) && $page_modification){
    foreach ($infos_participant as $cle => $valeur) {
        if(str_contains($cle, 'numero_compte_')){
            $numeros_comptes[] = $valeur;
        }
    }
}

foreach ($informations_bancaires as $champ => $intitule_champ) {
    if (!array_key_exists($champ, $_POST)) {
        // Le champ attendu est manquant
        // redirigerVersPageErreur(404, $current_url);
    } else {
        // Le champ est bien là
        $valeur_champ = $_POST[$champ];

        if (empty($valeur_champ)) {
            $erreurs[$champ][] = "Veuillez remplir ce champ";
        } else {
            if (str_contains($champ, 'banque_')) {
                if (preg_match('/^[p{L} -]+$/u', $valeur_champ)) {
                    $erreurs[$champ][] = "Ce champ doit être une chaîne de caractères alphabétiques !";
                } elseif (strlen($valeur_champ) > 100) {
                    $erreurs[$champ][] = "La valeur de ce champ ne doit pas excéder 100 caractères";
                }
            } elseif (str_contains($champ, 'numero_compte_')) {

                // Vérifier la validite du numéro de compte
                $_POST[$champ] = strtoupper($_POST[$champ]);

                if (preg_match('/[^A-Z0-9]+/', $valeur_champ)) {
                    // La valeur reçue contient d'autres caractères que les lettres et les chiffres
                    $erreurs[$champ][] = "Ce champ doit contenir uniquement des lettres et des chiffres";
                } elseif (!preg_match('/[0-9]+/', $valeur_champ)) {
                    // La valeur reçue ne contient aucun chiffre
                    $erreurs[$champ][] = "Ce champ doit contenir au moins un chiffre";
                } elseif (!preg_match('/[A-Z]+/', $valeur_champ)) {
                    // La valeur reçue ne contient aucune lettre
                    $erreurs[$champ][] = "Ce champ doit contenir au moins un caractère alphabétique";
                } else {
                    // Les valeurs indiquées sont valides au regard de l'alphabet

                    if ($nombre_comptes_bancaires != 1) {
                        // Le nombre de comptes bancaires dépasse 1 donc il faut s'assurer que leurs valeurs respectives sont différentes
                        $positions_occurences = array_keys($_POST, $valeur_champ, true); // Nous donne toutes les input (ou plus précisément les name des inputs) qui ont la même valeur que celle du numéro de compte en cours d'analyse. En définitive on aura des résultats comme numero_compte_1, numero_compte_2, etc.
                        $positions_occurences = array_filter($positions_occurences, function ($val) {
                            return strpos($val, 'numero_compte') !== false;
                        }); // Réduit les résultats trouvés uniquement aux inputs concernant les numéros de compte
                        $positions_occurences = array_values($positions_occurences); // Réarrange le tableau précédent pour ordonner les index

                        if (count($positions_occurences) > 1) {
                            // Le même numéro de compte apparaît plus d'une fois. On sauvegarde les champs concernés

                            if (!isset($occurences_numeros_compte)) {
                                $occurences_numeros_compte[0] = $positions_occurences;
                            } else {
                                if (!in_array($positions_occurences, $occurences_numeros_compte, true)) {
                                    $occurences_numeros_compte[] = $positions_occurences;
                                }
                            }
                            // Donc ici j'ai normalement dans $occurences_numeros_compte un ou des tableaux qui contiennent les champs contenant les mêmes valeurs
                        }
                    }

                    // On vérifie à présent que le numéro de compte indiqué n'existe pas déjà en bdd

                    $stmt = $bdd->prepare("SELECT numero_compte FROM informations_bancaires WHERE numero_compte = :val");
                    $stmt->bindParam('val', $valeur_champ);
                    $stmt->execute();
                    $ligne = $stmt->fetch(PDO::FETCH_NUM);
                    if ($ligne) {
                        // Une ligne a été retrouvée
                        $numero_retrouve = $ligne[0];
                        $message = "Le compte bancaire associé à ce numéro de compte a déjà été enregistré. Saisissez-en un autre !";

                        if(!isset($page_modification)){
                            $erreurs[$champ][] = $message;
                        }elseif(!in_array($numero_retrouve, $numeros_comptes)){
                            $erreurs[$champ][] = $message;
                        }
                    }
                }
            }
        }
    }
}

// Messages d'erreurs si les numéros de compte ne sont pas uniques

if (isset($occurences_numeros_compte)) {
    foreach ($occurences_numeros_compte as $occurence) {
        foreach ($occurence as $champ) {
            $erreurs[$champ][] = "Le numéro de compte doit être unique dans le formulaire !";
        }
    }
}

/** Traitement des fichiers */

foreach ($fichiers_attendus as $fichier) {

    // 1- Vérifier tout d'abord la présence du fichier en cours d'analyse
    if (!array_key_exists($fichier, $_FILES)) {
        // Le fichier est absent
        // redirigerVersPageErreur(404, $current_url);
    } else {
        // Le fichier est présent
        $infos_fichier = $_FILES[$fichier];

        if ($infos_fichier['error'] != 0) {
            // On vérifie les erreurs possibles
            $type_erreur = $infos_fichier['error'];
            if((isset($page_modification) && $type_erreur !=4) || (!isset($page_modification))){
                $erreurs[$fichier][] = $erreursUploadFichier[$type_erreur];
            }
        } elseif ($infos_fichier['size'] > $taille_admissible_fichiers_pdf) {
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
}
