<?php

// On inclut les entêtes liées aux informations générales

if (in_array('infos_generales', $elements_a_inclure)) {

    /** Traitement des informations textuelles */

    if (isset($page_modification) && $page_modification) {
        $matricule_ifu = $infos_participant['matricule_ifu'];
    }

    foreach ($informations_generales as $champ => $intitule_champ) {
        // 1- Vérifier tout d'abord la présence de tous les champs attendus

        if (!array_key_exists($champ, $_POST)) {
            // Si un champ est manquant, on dirige vers la page 404 tout simplement
            // redirigerVersPageErreur(404, $current_url);
        } else {
            // Le champ ne manque pas à l'appel
            // 2- S'assurer à présent qu'il n'est pas vide

            $valeur_champ = $_POST[$champ];

            if (empty($valeur_champ)) {
                $erreurs[$champ][] = "Veuillez remplir ce champ";
            } else {
                // Le champ en cours n'est pas vide
                if ($champ == "nom" || $champ == "prenoms" || $champ == "lieu_naissance" || $champ == "diplome") {
                    if (preg_match('/[^\p{L} -]/u', $valeur_champ)) {
                        $erreurs[$champ][] = "Ce champ doit être une chaîne de caractères alphabétiques !";
                    } elseif (strlen($valeur_champ) > 100) {
                        $erreurs[$champ][] = "La valeur de ce champ ne doit pas excéder 100 caractères";
                    }
                } elseif ($champ == "matricule_ifu") {
                    // Je vais partir du principe que lui est comme le numéro de compte soit uniquement des lettres, des tirets mais éventuellement aussi des tirets. Après on pourra peaufiner si le besoin s'en fait ressentir
                    if (preg_match('/[^a-zA-Z0-9-]/', $valeur_champ)) {
                        $erreurs[$champ][] = "Ce champ doit contenir uniquement des lettres, des chiffres et des tirets";
                    } else {
                        // La valeur semble valide mais vérifions si elle se retrouve ou non dans la base de données

                        $stmt = $bdd->prepare("SELECT matricule_ifu FROM participants WHERE matricule_ifu = :val");
                        $stmt->bindParam('val', $valeur_champ);
                        $stmt->execute();
                        $ligne = $stmt->fetch(PDO::FETCH_NUM);

                        if ($ligne) {
                            $matricule_retrouve = $ligne[0];
                            if (!isset($page_modification) || (isset($page_modification) && $matricule_ifu != $matricule_retrouve)) {
                                // 1er cas de figure : Une ligne a été retrouvée dans la bdd et nous sommes supposés être sur la page d'ajout d'un acteur donc le matricule a déjà été associé à un autre utilisateur et on ne peut permettre qu'il soit réassigné
                                // 2ème cas de figure : Nous sommes sur la page de modification mais le matricule reçu correspond à celui d'un acteur qui n'est pas celui dont on modifie les informations donc c'est off
                                $erreurs[$champ][] = "La valeur indiquée existe déjà. Le matricule/IFU est supposé unique par acteur !";
                            }
                        }
                    }
                } else if ($champ == "date_naissance") {
                    // On vérifie la validité de la date. Les données de l'input date viennent sous le format année-mois-jour
                    $message = "La date que vous avez indiquée est invalide !";
                    $date_tableau = explode('-', $valeur_champ);

                    if (!count($date_tableau) == 3) {
                        // Problème, la valeur reçue n'est pas suivant le format attendu
                        $erreurs[$champ][] = $message;
                    } else {
                        if (!checkdate($date_tableau[1], $date_tableau[2], $date_tableau[0])) {
                            // Date invalide tout simplement comme un 31 février
                            $erreurs[$champ][] = $message;
                        } else {
                            // On vérifie à présent si la date est inférieur à il y a au moins 18 ans
                            $date_indiquee = mktime(0, 0, 0, $date_tableau[1], $date_tableau[2], $date_tableau[0]);
                            $date_reference = mktime(0, 0, 0, date("m"), date("d"), date("y") - 18);

                            if ($date_indiquee >= $date_reference) {
                                // Cela veut dire que le participant est né il y a moins de 18ans, ce qui est anormal
                                $erreurs[$champ][] = "L'acteur que vous souhaitez enregistrer semble avoir moins de 18 ans !";
                            }
                        }
                    }
                } elseif ($champ == 'reference_carte_identite') {
                    // La référence de la carte d'identité est une simple suite de chiffres donc il faut que la valeur reçue y corresponde
                    if (preg_match('/[^0-9]/', $valeur_champ)) {
                        $erreurs[$champ][] = "Ce champ ne peut prendre qu'une succession de chiffres";
                    }

                    if (isset($page_modification)) {
                        // Récupérons la référence de l'acteur dont on veut modifier les informations
                        $stmt = $bdd->prepare('SELECT reference_carte_identite FROM participants WHERE id_participant=' . $id_participant);
                        $stmt->execute();
                        $reference_acteur = $stmt->fetch(PDO::FETCH_NUM)[0];
                    }

                    // On vérifie à présent si cette référence n'existait pas déjà en bdd
                    $stmt = $bdd->prepare('SELECT reference_carte_identite FROM participants WHERE reference_carte_identite =:val');
                    $stmt->execute(['val' => $valeur_champ]);
                    $ligne = $stmt->fetch(PDO::FETCH_NUM);
                    if ($ligne) {
                        $reference_retrouvee = $ligne[0];
                        if (!isset($page_modification) || (isset($page_modification) && $reference_retrouvee != $reference_acteur)) {
                            // Quelque chose de similaire a été fait avec le matricule, relis l'explication là-bas pour capter le process ici
                            $erreurs[$champ][] = 'La référence que vous avez indiquée a déjà été attribuée à un autre acteur';
                        }
                    }
                }
            }
        }
    }
}

// On inclut les entêtes liées aux comptes bancaires

if (in_array('infos_bancaires', $elements_a_inclure)) {

    /** Traitement des informations bancaires et des fichiers */

    if (isset($page_modification) && $page_modification) {
        foreach ($infos_participant as $cle => $valeur) {
            if (str_contains($cle, 'numero_compte_')) {
                $numeros_comptes[] = $valeur;
            }
        }
    }

    foreach ($informations_bancaires as $champ => $intitule_champ) {
        if (!array_key_exists($champ, $_POST)) {
            // Le champ attendu est manquant. D'une certaine manière on rattrape cette anomalie avec le empty() en dessous donc on peut laisser en commentaires
            // redirigerVersPageErreur(404, $current_url);
        } else {
            // Le champ est bien là
            $valeur_champ = $_POST[$champ];

            if (empty($valeur_champ)) {
                $erreurs[$champ][] = "Veuillez remplir ce champ";
            } else {
                if (str_contains($champ, 'banque_')) {
                    if (preg_match('/[^\p{L} -]+$/u', $valeur_champ)) {
                        $erreurs[$champ][] = "Ce champ doit être une chaîne de caractères alphabétiques !";
                    } elseif (strlen($valeur_champ) > 100) {
                        $erreurs[$champ][] = "La valeur de ce champ ne doit pas excéder 100 caractères";
                    }
                } elseif (str_contains($champ, 'numero_compte_')) {

                    // Vérifier la validite du numéro de compte
                    $_POST[$champ] = strtoupper($_POST[$champ]);
                    $valeur_champ = $_POST[$champ];

                    if (!preg_match('/^[A-Z0-9]+$/', $valeur_champ)) {
                        // La valeur reçue contient d'autres caractères que les lettres et les chiffres
                        $erreurs[$champ][] = "Ce champ doit contenir uniquement des lettres et des chiffres";
                    } elseif (!preg_match('/[0-9]/', $valeur_champ)) {
                        // La valeur reçue ne contient aucun chiffre
                        $erreurs[$champ][] = "Ce champ doit contenir au moins un chiffre";
                    } elseif (!preg_match('/[A-Z]/', $valeur_champ)) {
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

                        $stmt = $bdd->prepare("
                        SELECT numero_compte 
                        FROM informations_bancaires ib
                        INNER JOIN participants p ON p.id_participant=ib.id_participant
                        WHERE numero_compte = :val AND p.id_user=" . $_SESSION['user_id'] . "
                        ");
                        $stmt->bindParam('val', $valeur_champ);
                        $stmt->execute();
                        $ligne = $stmt->fetch(PDO::FETCH_NUM);
                        if ($ligne) {
                            // Une ligne a été retrouvée
                            $numero_retrouve = $ligne[0];
                            $message = "Le compte bancaire associé à ce numéro de compte a déjà été enregistré. Saisissez-en un autre !";

                            if (!isset($page_modification)) {
                                $erreurs[$champ][] = $message;
                            } elseif (!in_array($numero_retrouve, $numeros_comptes)) {
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
            // Ici pas besoin de mettre un message, ce cas est aussi pris en compte dans $erreursUploadFichier
        } else {
            // Le fichier est présent
            $infos_fichier = $_FILES[$fichier];

            if ($infos_fichier['error'] != 0) {
                // On vérifie les erreurs possibles
                $type_erreur = $infos_fichier['error'];
                if ((isset($page_modification) && $type_erreur != 4) || (!isset($page_modification))) {
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
                    $erreurs[$fichier][] = "Le fichier attendu est de type PDF";
                }
            }
        }
    }

    // Bien, nous allons ajouter une clause pour rendre le remplissage des informations bancaires facultatif sur la page d'ajout de l'acteur
    if (isset($page_ajout_participant) && $page_ajout_participant) {
        // Nous sommes sur la bonne page. Nous allons procéder de façon inverse. On ne touche rien à la logique précédemment mise en place donc si les informations bancaires ne sont pas remplies, le script attribuera à chacun de ces champs un message d'erreur. Ici donc nous allons travailler en fonction de ce message d'erreur et partir du principe que si ce message d'erreur existe, c'est que le champ en particulier est vide. Aussi, nous n'avons que trois champs donc on pourra poser les conditions sur chacune d'elle sans trop de peine.
        $message_erreur = "Veuillez remplir ce champ";
        $message_erreur_2 = "Aucun fichier sélectionné";
        if ((isset($erreurs['banque_1']) && $erreurs['banque_1'][0] == $message_erreur) && (isset($erreurs['numero_compte_1']) && $erreurs['numero_compte_1'][0] == $message_erreur) && (isset($erreurs['pdf_rib_1']) && $erreurs['pdf_rib_1'][0] == $message_erreur_2)) {
            // Cela veut dire qu'aucun des trois champs n'a été rempli et dans ce contexte, on peut skipper les erreurs et permettre l'ajout de l'acteur
            unset($erreurs['banque_1']);
            unset($erreurs['numero_compte_1']);
            unset($erreurs['pdf_rib_1']);

            if (empty($erreurs)) {
                // Bh il semble qu'il n'y avait que ces erreurs dans le tableau erreurs donc autant delete $erreurs
                unset($erreurs);
            }
        }
    }
}

// Bon, vérifions à présent si les informations dont nous disposons, supposées valides existent déjà ou pas
if (!isset($erreurs)) {
    // On vérifie si un participant relativement identique n'est pas déjà présent en bdd. Mais là il faut tenir compte de deux cas : le cas de la page d'ajout où cette vérification est tout à fait légitime et le cas de la page de modification où si aucune modification n'est effectuée, la requête nous donnera une positivité sans qu'elle ne soit pour autant pertinente puisque c'est l'acteur dont on modifie les modifications qui aura été détecté par la requête donc il faut trouver le moyen de ne pas exécuter la requête si aucune modification n'est effectuée. Mais comment ?
    // En principe sur la page de modification des informations, nous disposons d'une variable $infos_participant qui contient les informations de base de l'acteur dont on veut modifier les informations. De là, il suffira de comparer les informations présentes dans la POST et celles présentes dans cette variable. Puis nous compterons le nombre de comparaisons positives retrouvées. Si le compte final correspond à la taille de la variable $informations_generales (autrement dit, toutes les informations générales sont restées identiques), alors il n'y a pas eu de modifications et nous pouvons ensuite définir une variable nous renseignant sur la chose. Si par-contre le compte est différent (et nécessairement inférieur) alors il y a eu une ou des modification(s) et dans ce cas la requête pourra être exécutée sans problèmes apparents.

    if (isset($page_modification)) {
        $compteur = 0;
        foreach ($_POST as $cle => $valeur) {
            if (isset($infos_participant[$cle]) && ($_POST[$cle] == $infos_participant[$cle])) $compteur++;
        }
        if ($compteur == count($informations_generales)) $pas_de_modifications = true;
        else $pas_de_modifications = false;
    }

    if (isset($page_ajout_participant) || (isset($page_modification) && !$pas_de_modifications)) {
        $stmt = $bdd->prepare('
        SELECT id_participant
        FROM participants
        WHERE
        nom=:nom AND
        prenoms=:prenoms AND
        date_naissance=:date_naissance AND
        lieu_naissance=:lieu_naissance AND
        diplome_le_plus_eleve=:diplome_le_plus_eleve AND
        reference_carte_identite=:reference
        ');

        $stmt->execute([
            'nom' => $_POST['nom'],
            'prenoms' => $_POST['prenoms'],
            'date_naissance' => $_POST['date_naissance'],
            'lieu_naissance' => $_POST['lieu_naissance'],
            'diplome_le_plus_eleve' => $_POST['diplome_le_plus_eleve'],
            'reference' => $_POST['reference_carte_identite']
        ]);

        if ($stmt->rowCount() != 0) {
            $erreurs['doublon'] = 'Il semble que vous avez déjà enregistré un acteur avec des informations très similaires';
        }
    }
}
