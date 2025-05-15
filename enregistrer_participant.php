<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un participant</title>
    <link rel="stylesheet" href="assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
</head>

<body>

    <div class="container-md">

        <?php
        require_once "includes/bdd.php";
        $message_erreur = "<div class=\"alert alert-danger mt-4\"><h4>Oups, une erreur s'est produite !</h4></div>";

        // 1- Vérifier que l'id est bien là
        if (!isset($_GET['id_activite'])) { // L'id de l'activite n'est pas présent
            echo $message_erreur;
        } else {
            // 2 - Vérifier que c'est bien un nombre que j'ai
            $id_activite = intval($_GET['id_activite']);
            if ($id_activite == 0) // C'est une chaîne de caractères que j'ai reçue
            {
                echo $message_erreur;
            } else {
                // 3- Vérifier que cet id se retrouve dans la base de données et récupérer par la même occasion le type de cette activité
                $stmt = $bdd->prepare("SELECT id, type_activite FROM activites WHERE id = :id"); // stmt pour statement
                $stmt->bindParam(':id', $id_activite, PDO::PARAM_INT);
                $stmt->execute();

                $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$ligne) {
                    // Aucune ligne trouvée donc l'id n'est pas enregistrée dans la base de données
                    echo $message_erreur;
                } else {
                    // Id retrouvé donc tout va bien, on passe à la vérification du nombre de comptes bancaires
                    $type_activite = $ligne['type_activite'];
                    $nombre_maximal_comptes_bancaires = 5;

                    if (!isset($_GET['nombre_comptes_bancaires'])) {
        ?>
                        <h4 class="my-4">Page d'ajout d'un participant</h4>
                        <h5>Phase préliminaire</h5>
                        <hr>
                        <p>Avant de débuter l'enregistrement des informations du participant, pouvez-vous nous donner le nombre de numéros bancaires dispose le participant ?</p>

                        <form action="" method="get">
                            <div class="mb-2 row">
                                <label for="nombre_comptes_bancaires" class="col-sm-5 col-form-label">Nombre de comptes bancaires du participant : </label>
                                <div class="col-sm-7">
                                    <input type="hidden" name="id_activite" value="<?php echo $id_activite; ?>">
                                    <input type="number" name="nombre_comptes_bancaires" id="nombre_comptes_bancaires" class="form-control" placeholder="Indiquez le nombre" max="<?php echo $nombre_maximal_comptes_bancaires; ?>" min="1" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary my-2">Envoyer</button>
                        </form>

                        <?php
                    } else {
                        // Tests de validation sur le nombre de comptes bancaires reçus
                        $nombre_valide = false;
                        $valeur = intval($_GET['nombre_comptes_bancaires']); // Si la valeur qu'on reçoit est une chaîne de caractères, intval() va retourner 0
                        // 1- Vérifier la présence du champ : ça se fait automatiquement, si le champ est absent on revient à la première étape pour prendre le nombre
                        // 2- Vérifier si le champ n'est pas vide

                        if (!empty($valeur)) // Champ non vide
                        {
                            // 3- Vérifier que j'ai bien reçu un nombre (donc que la valeur reçue n'est pas 0) et que ce dernier est bien dans la fourchette autorisée
                            if ($valeur != 0 && $valeur >= 1 && $valeur <= $nombre_maximal_comptes_bancaires) {
                                $nombre_valide = true;
                            }
                        }
                        if (!$nombre_valide) // Nombre invalide
                        {
                            echo $message_erreur;
                        } else {
                            // Le nombre de comptes bancaires est valide alors la page peut s'afficher normalement

                            // Récupération de toute la table participants ou pas...

                            // Bref

                            $nombre_comptes_bancaires = $_GET['nombre_comptes_bancaires'];
                            $taille_admissible_fichiers_pdf = 2e6; // (2Mo)
                            $extensions_autorisees = array('pdf');
                            $erreursUploadFichier = array(
                                0 => "Il n\'y a pas d'erreur, le téléversement s'est déroulé avec succès.",
                                1 => "La taille du fichier sélectionné excède la taille maximale prévue dans le fichier php.ini.",
                                2 => "La taille du fichier excède la taille maximale prévue : " . $taille_admissible_fichiers_pdf / 1e6 . " Mo.",
                                3 => "Le fichier sélectionné a seulement été partiellement téléversé.",
                                4 => "Aucun fichier sélectionné",
                                6 => "Un dossier temporaire manquant.",
                                7 => "Impossible d'écrire sur le disque dur.",
                                8 => "Une extension PHP a empêché le téléversement du fichier"
                            );

                            // Informations de sauvegarde des fichiers

                            $repertoire_racine = __DIR__ . "/fichiers";
                            $permissions = 0777;


                            $champs_attendus = [
                                "nom" => [
                                    "intitule" => "Nom",
                                    "valide" => false
                                ],
                                "prenoms" => [
                                    "intitule" => "Prénoms",
                                    "valide" => false
                                ],
                                "matricule_ifu" => [
                                    "intitule" => "Matricule/IFU",
                                    "valide" => false
                                ],
                                "date_naissance" => [
                                    "intitule" => "Date de naissance",
                                    "valide" => false
                                ],
                                "lieu_naissance" => [
                                    "intitule" => "Lieu de naissance",
                                    "valide" => false
                                ],
                                "diplome" => [
                                    "intitule" => "Diplôme",
                                    "valide" => false
                                ],
                                "role" => [
                                    "intitule" => "Titre",
                                    "valide" => false
                                ],
                                "nbr_jours" => [
                                    "intitule" => "Nombre de jours de travail",
                                    "valide" => false
                                ]
                            ];

                            for ($i = 1; $i <= $nombre_comptes_bancaires; $i++) {

                                $champs_attendus["banque_$i"]["intitule"] = "Banque ($i)";
                                $champs_attendus["banque_$i"]["valide"] = false;

                                $champs_attendus["numero_compte_$i"]["intitule"] = "Numéro de compte ($i)";
                                $champs_attendus["numero_compte_$i"]["valide"] = false;

                                $fichiers_attendus["pdf_rib_$i"]["intitule"] = "Copie PDF du RIB ($i)";
                                $fichiers_attendus["pdf_rib_$i"]["valide"] = false;
                            }

                            if ($type_activite == 3) {
                                $champs_attendus["nbr_copies"]['intitule'] = "Nombre de copies";
                                $champs_attendus["nbr_copies"]['valide'] = false;
                            }

                            // Obtention des diplômes
                            $stmt = $bdd->prepare("SELECT id_diplome, nom FROM diplomes WHERE id_activite = :id ORDER BY nom");
                            $stmt->bindParam(':id', $id_activite, PDO::PARAM_INT);
                            $stmt->execute();

                            //echo count($stmt);

                            foreach ($stmt as $ligne) {
                                $diplomes['intitules'][] = $ligne['nom'];
                                $diplomes['ids'][] = $ligne['id_diplome'];
                            }

                            $stmt->closeCursor();

                            // Obtention des rôles
                            $stmt = $bdd->prepare("SELECT id_titre, nom FROM titres WHERE id_activite = :id ORDER BY nom");
                            $stmt->bindParam(':id', $id_activite, PDO::PARAM_INT);
                            $stmt->execute();

                            foreach ($stmt as $ligne) {
                                $roles['intitules'][] = $ligne['nom'];
                                $roles['ids'][] = $ligne['id_titre'];
                            }
                            $stmt->closeCursor();

                            // Validation des informations

                            if (isset($_POST['enregistrer'])) // Formulaire bien envoyé
                            {
                                /* Traitement des informations textuelles */

                                foreach ($champs_attendus as $champ => $valeur) {
                                    // 1- Vérifier tout d'abord la présence de tous les champs attendus

                                    if (!isset($_POST[$champ])) {
                                        echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite, le champ \"" . $valeur['intitule'] . " est absent !</div>";
                                    } else {
                                        // 2 - Vérifier si les champs ne sont pas vides

                                        if (empty($_POST[$champ])) {
                                            $erreurs[$champ][] = "Veuillez remplir ce champ !";
                                        } else {
                                            // Vérifications spécifiques

                                            // 3 - Echapper les valeurs reçues (ce n'est plus nécessaire en raison de l'expression régulière employées)
                                            if ($champ == "nom" || $champ == "prenoms" || $champ == "lieu_naissance" || str_contains($champ, 'banque_')) {
                                                if (!preg_match('/^[p{L} -]+$/u', $_POST[$champ])) {
                                                    $erreurs[$champ][] = "Ce champ doit être une chaîne de caractères alphabétiques !";
                                                } elseif (strlen($champ) > 50) {
                                                    $erreurs[$champ][] = "La valeur de ce champ ne doit pas excéder 50 caractères";
                                                } else {
                                                    // Tout va bien
                                                    $champs_attendus[$champ]['valide'] = true;
                                                }

                                                /* Explication du regex 
                                                - ^ et $ : début et fin de chaîne (s'assure que toute la chaîne est validée)
                                                - [p{L} ] : autorise toutes les lettres Unicode (\p{L} ) et les espaces
                                                - + : au moins un caractère
                                                - u : indique l'encodage UTF-9, nécessaire pour bien gérer les accents
                                                */
                                            } elseif ($champ == "matricule_ifu") {
                                                // Je vais partir du principe que lui il est comme le numéro de compte, il doit contenir des lettres, des chiffres et éventuellement des tirets. Après je pourrai peaufiner s'il le faut
                                                if (preg_match('/[^a-zA-Z0-9-]+/', $_POST[$champ])) {
                                                    $erreurs[$champ][] = "Ce champ ne peut contenir que des lettres, des chiffres et des tirets";
                                                } else {
                                                    // La valeur semble valide mais je vais checker à présent si elle se retrouve ou non dans la base de données

                                                    $stmt = $bdd->prepare("SELECT matricule_ifu FROM participants WHERE matricule_ifu = :id"); // stmt pour statement
                                                    $stmt->bindParam(':id', $_POST[$champ], PDO::PARAM_INT);
                                                    $stmt->execute();
                                                    $ligne = $stmt->fetch(PDO::FETCH_ASSOC);

                                                    if ($ligne) {
                                                        // Une a été retrouvée
                                                        $erreurs[$champ][] = "La valeur indiquée existe déjà. Le matricule/ifu est supposé unique !";
                                                    } else {
                                                        $champs_attendus[$champ]['valide'] = true;
                                                    }
                                                }
                                            } elseif ($champ == "date_naissance") {

                                                // 4 - Vérifier la validité de la date

                                                // Les données de la date viennent sous le format année-mois-jour

                                                $message = "La date que vous avez indiquée est invalide !";
                                                $date_tableau = explode('-', $_POST[$champ]);

                                                if (!count($date_tableau) == 3) {
                                                    // Problème avec la valeur reçue, elle n'est pas suivant le format année-mois-jour attendu
                                                    $erreurs[$champ][] = $message;
                                                } else {
                                                    if (!checkdate($date_tableau[1], $date_tableau[2], $date_tableau[0])) {
                                                        // Date invalide tout simplement
                                                        $erreurs[$champ][] = $message;
                                                    } else {
                                                        // 4.1 - Vérifier si la date est au moins inférieure à il y a 18 ans
                                                        $date_indiquee = mktime(0, 0, 0, $date_tableau[1], $date_tableau[2], $date_tableau[0]);
                                                        $date_reference = mktime(0, 0, 0, date("m"), date("d"), date("y") - 18);

                                                        if ($date_indiquee >= $date_reference) {
                                                            // Cela veut dire que le participant est né il y a moins de 18 ans ce qui est anormal
                                                            $erreurs[$champ][] = $message;
                                                        } else {
                                                            // La date n'a pas de problèmes apparants
                                                            $champs_attendus[$champ]['valide'] = true;
                                                        }
                                                    }
                                                }
                                            } elseif ($champ == "diplome") {
                                                // 5- Validité du diplôme reçu

                                                if (preg_match('/^[p{L} -]+$/u', $_POST[$champ])) {
                                                    $erreurs[$champ][] = "Ce champ doit être une chaîne de caractères alphabétiques !";
                                                } elseif (strlen($champ) > 50) {
                                                    $erreurs[$champ][] = "La valeur de ce champ ne doit pas excéder 50 caractères";
                                                } elseif (!in_array($_POST[$champ], $diplomes['intitules'])) {
                                                    $erreurs[$champ][] = "Le diplome que vous avez choisi n'est pas valide !";
                                                } else {
                                                    // Le diplôme choisi est bien dans la liste des diplômes attendus
                                                    // $id_diplome = array_search($_POST['diplome'], $diplomes['intitules']);
                                                    // $id_diplome = $diplomes['ids'][$id_diplome];
                                                    // echo $id_diplome;
                                                    $champs_attendus[$champ]['valide'] = true;
                                                }
                                            } elseif ($champ == "role") {
                                                // 6 - Vérifier la validité du rôle reçu
                                                if (preg_match('/^[p{L} -]+$/u', $_POST[$champ])) {
                                                    $erreurs[$champ][] = "Ce champ doit être une chaîne de caractères alphabétiques !";
                                                } elseif (strlen($champ) > 50) {
                                                    $erreurs[$champ][] = "La valeur de ce champ ne doit pas excéder 50 caractères";
                                                } elseif (!in_array($_POST[$champ], $roles['intitules'])) {
                                                    $erreurs[$champ][] = "Le titre que vous avez choisi n'est pas valide !";
                                                } else {
                                                    // Le titre choisi est bien valide
                                                    $champs_attendus[$champ]['valide'] = true;
                                                }
                                            } elseif (str_contains($champ, 'numero_compte_')) {
                                                // 7- Vérifier la validité du numéro de compte
                                                $message = "Ce champ doit contenir uniquement des lettres et des chiffres";
                                                $_POST[$champ] = strtoupper($_POST[$champ]);

                                                if (preg_match('/[^A-Z0-9]+/', $_POST[$champ])) {
                                                    // La valeur reçue contient d'autres caractères que les lettres et les chiffres
                                                    $erreurs[$champ][] = "Ce champ doit contenir uniquement des lettres et des chiffres";
                                                } elseif (!preg_match('/[0-9]+/', $_POST[$champ])) {
                                                    // La valeur reçue ne contient aucun chiffre
                                                    $erreurs[$champ][] = "Ce champ doit contenir au moins un chiffre";
                                                } elseif (!preg_match('/[A-Z]+/', $_POST[$champ])) {
                                                    // La valeur reçue ne contient aucune lettre
                                                    $erreurs[$champ][] = "Ce champ doit contenir au moins un caractère alphabétique";
                                                } else {
                                                    // Les valeurs indiquées sont valides au regard de l'alphabet mais la vérification se poursuit quand même
                                                    // 8 - S'assurer que chaque numéro de compte est unique
                                                    $valeur = $_POST[$champ];
                                                    $positions_occurences = array_keys($_POST, $valeur, true); // Nous donne toutes les input qui ont la même valeur que celle du numéro de compte en cours d'analyse
                                                    $positions_occurences = array_filter($positions_occurences, function ($val) {
                                                        return strpos($val, 'numero_compte') !== false;
                                                    }); // Réduit les résultats trouvés uniquement aux inputs concernant les numéros de compte
                                                    $positions_occurences = array_values($positions_occurences); // Réarrange le tableau précédent pour ordonner les index

                                                    if (count($positions_occurences) > 1) {
                                                        // Si le même numéro de compte apparaît plus d'une fois on sauvegarde les champs dans lesquels il apparaît pour afficher les erreurs associées
                                                        if (!isset($occurences_numeros_compte)) {
                                                            $occurences_numeros_compte[0] = $positions_occurences;
                                                        } else {
                                                            if (!in_array($positions_occurences, $occurences_numeros_compte, true)) {
                                                                $occurences_numeros_compte[] = $positions_occurences;
                                                            }
                                                        }
                                                    } else {
                                                        // La valeur n'est pas apparue plus d'une fois donc elle est unique
                                                        $champs_attendus[$champ]['valide'] = true;
                                                    }
                                                }
                                            } elseif ($champ == "nbr_jours" || $champ == "nbr_copies") {
                                                // Je suis supposé recevoir des nombres donc c'est essentiellement ce test que je vais faire
                                                if (preg_match('/[^0-9]+/', $_POST[$champ])) {
                                                    // Il y a d'autres valeurs que des chiffres et c'est un problème
                                                    $erreurs[$champ][] = "La valeur indiquée n'est pas valide !";
                                                } else {
                                                    // Je n'ai que des chiffres
                                                    $champs_attendus[$champ]['valide'] = true;
                                                }
                                            }
                                        }
                                    }
                                }

                                // Messages d'erreurs si les numéros de compte ne sont pas uniques dans le formulaire

                                if (isset($occurences_numeros_compte)) {
                                    for ($i = 0; $i < count($occurences_numeros_compte); $i++) {
                                        $champs_identiques = $occurences_numeros_compte[$i];
                                        $message = "Les numéros de compte ";

                                        for ($j = 0; $j < count($champs_identiques); $j++) {
                                            $champ = $champs_identiques[$j];

                                            if ($j == 0) {
                                                // Premier champ
                                                $message = $message . substr($champs_attendus[$champ]['intitule'], -2, 1);
                                            } elseif ($j == count($champs_identiques) - 1) {
                                                // Dernier champ
                                                $message = $message . " et " . substr($champs_attendus[$champ]['intitule'], -2, 1);
                                            } else {
                                                // Autres champs
                                                $message = $message . ", " . substr($champs_attendus[$champ]['intitule'], -2, 1);
                                            }
                                        }
                                        $message = $message . " sont identiques !";
                                        echo "<div class=\"alert alert-danger mt-2\">" . $message . "</div>";
                                    }
                                }

                                /* Traitement des fichiers */

                                foreach ($fichiers_attendus as $fichier => $infos) {
                                    // 1- Vérifier tout d'abord la présence des fichiers attendus
                                    if (!isset($_FILES[$fichier])) {
                                        // Si un des fichiers attendus est absent
                                        echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite avec le fichier : \"" . $infos['intitule'] . "\" !</div>";
                                    } elseif ($_FILES[$fichier]['error'] != 0) {
                                        // 2 - Vérifier les erreurs possibles dont la taille de fichier
                                        $type_erreur = $_FILES[$fichier]['error'];
                                        $erreurs[$fichier][] = $erreursUploadFichier[$type_erreur];
                                    } elseif ($_FILES[$fichier]['size'] > $taille_admissible_fichiers_pdf) {
                                        // La taille du fichier n'est pas celle permise
                                        $erreurs[$fichier][] = $erreursUploadFichier[2];
                                    } else {
                                        // 3- Vérifier l'extension du fichier
                                        $extension_upload = strtolower(pathinfo($_FILES[$fichier]['name'], PATHINFO_EXTENSION));

                                        if (!in_array($extension_upload, $extensions_autorisees)) {
                                            // Le fichier n'a pas la bonne extension
                                            $erreurs[$fichier][] = "Le fichier attendu est de type PDF !";
                                        } else {
                                            // Le fichier est relativement valide
                                            $fichiers_attendus[$fichier]['valide'] = true;
                                        }
                                    }
                                }


                                /* Préparatifs pour l'enregistrement des données */

                                // Bon on attaque la validation globale
                                $donnees_valides = true;

                                foreach ($champs_attendus as $champ => $infos) {
                                    if ($infos['valide'] == false) {
                                        // echo $infos['intitule'];
                                        // echo "<br>";
                                        $donnees_valides = false;
                                    }
                                }
                                foreach ($fichiers_attendus as $fichier => $infos) {
                                    if ($infos['valide'] == false) {
                                        // echo $infos['intitule'];
                                        // echo "<br>";
                                        $donnees_valides = false;
                                    }
                                }

                                if ($donnees_valides) {
                                    // Toutes les données attendues sont valides et on peut commencer l'enregistrement des informations

                                    // Création des dossiers s'ils n'existent pas

                                    $upload_annee = $repertoire_racine . "/" . date("Y");
                                    $upload_mois = $upload_annee . "/" . date("m");
                                    $upload_dirs = array($repertoire_racine, $upload_annee, $upload_mois);

                                    foreach ($upload_dirs as $dir) {
                                        if (!is_dir($dir)) {
                                            // Le dossier n'existe pas
                                            if (!mkdir($dir, $permissions)) {
                                                echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite lors de la création des dossiers de sauvegarde des fichiers. Vérifiez les permissions.</div>";
                                                die(-1);
                                            }
                                        }
                                    }

                                    // Enregisrement des données textuelles

                                    // Participants
                                    $stmt = $bdd->prepare("INSERT INTO participants(nom, prenoms, matricule_ifu, date_naissance, lieu_naissance, id_diplome) VALUES (:val1, :val2, :val3, :val4, :val5, :val6)");

                                    $stmt->bindParam(':val1', $_POST['nom']);
                                    $stmt->bindParam(':val2', $_POST['prenoms']);
                                    $stmt->bindParam(':val3', $_POST['matricule_ifu']);
                                    $stmt->bindParam(':val4', $_POST['date_naissance']);
                                    $stmt->bindParam(':val5', $_POST['lieu_naissance']);

                                    // Retrouver l'id correspondant au diplome choisi
                                    $id_diplome = array_search($_POST['diplome'], $diplomes['intitules']);
                                    $id_diplome = $diplomes['ids'][$id_diplome];

                                    $stmt->bindParam(':val6', $id_diplome, PDO::PARAM_INT);

                                    $resultat = $stmt->execute();

                                    if (!$resultat) {
                                        echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite lors de l'enregistrement des informations ! Rechargez la page pour tenter de le résoudre.</div>";
                                        die(-1);
                                    } else {
                                        // Le premier enregistremnt a été effectué.

                                        // Pour la suite j'ai besoin de l'id du participant donc je le récupère
                                        $id_participant = $bdd->lastInsertId();

                                        // Table fichiers

                                        $upload_path = $upload_mois . "/";

                                        foreach ($fichiers_attendus as $fichier => $infos) {
                                            // Pour chaque fichier présent dans le formulaire je fais :

                                            /*
                                                1- Je définis le nom qui va s'appliquer à ce fichier
                                                2- J'enregistre le fichier
                                                3- Je sauvegarde son id
                                                4- J'enregistre en même temps les informations de la table informations_bancaires
                                                 */

                                            // Prendre la chaîne de caractère "pdf_rib_$i", retirer et mettre de côté le $i dans cette chaîne, mettre le "matricule" puis recoller le "$i"
                                            $nom_fichier = $fichier; // Ici j'ai récupéré "pdf_rib_$i"

                                            $chiffre_fin = substr($nom_fichier, -1); // Je prends le chiffre de fin
                                            $nom_fichier = substr($nom_fichier, 0, -1); // Ici je garde "pdf_rib_";
                                            $nom_fichier = $nom_fichier . "_" . $_POST['matricule_ifu'] . "_" . $chiffre_fin . ".pdf"; // Je constitue le nom final

                                            //2- J'enregistre le fichier

                                            $chemin_absolu = $upload_path . $nom_fichier;

                                            if (move_uploaded_file($_FILES[$fichier]['tmp_name'], $chemin_absolu)) {

                                                // Enregistrement des métadonnées

                                                $stmt = $bdd->prepare("INSERT INTO fichiers(chemin_acces, nom_original, date_upload, type_fichier) VALUES (:val1, :val2, :val3, :val4)");

                                                $stmt->bindParam(':val1', $chemin_absolu);
                                                $stmt->bindParam(':val2', $_FILES[$fichier]['name']); // nom original
                                                $date_upload = date("Y-m-d H:i:s"); //  peut être : 2001-03-10 17:16:18 (le format DATETIME de MySQL)
                                                $stmt->bindParam(':val3', $date_upload);
                                                $extension = strtolower(pathinfo($_FILES[$fichier]['name'], PATHINFO_EXTENSION));
                                                $stmt->bindParam(':val4', $extension); // extension
                                                $resultat = $stmt->execute();

                                                if (!$resultat) {
                                                    echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite lors de l'enregistrement des informations ! Rechargez la page pour tenter de le résoudre.</div>";
                                                    die(-1);
                                                } else {
                                                    // Les métadonnées ont été enregistrées
                                                    // Je récupère l'id du fichier

                                                    $id_fichier = $bdd->lastInsertId();

                                                    // Table informations bancaires

                                                    $stmt = $bdd->prepare("INSERT INTO informations_bancaires(id_participant, banque, numero_compte, id_rib) VALUES (:val1, :val2, :val3, :val4)");

                                                    $stmt->bindParam(':val1', $id_participant);
                                                    $stmt->bindParam(':val2', $_POST['banque_' . $chiffre_fin]);
                                                    $stmt->bindParam(':val3', $_POST['numero_compte_' . $chiffre_fin]);
                                                    $stmt->bindParam(':val4', $id_fichier, PDO::PARAM_INT);

                                                    $resultat = $stmt->execute();

                                                    if (!$resultat) {
                                                        echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite lors de l'enregistrement des informations ! Rechargez la page pour tenter de le résoudre.</div>";
                                                        die(-1);
                                                    } else {
                                                        // Les informations bancaires sont ok

                                                        // Dernière table : participations

                                                        $stmt = $bdd->prepare("INSERT INTO participations(id_participant, id_activite, id_titre, nombre_jours, nombre_taches) VALUES (:val1, :val2, :val3, :val4, :val5)");

                                                        $stmt->bindParam(':val1', $id_participant, PDO::PARAM_INT);
                                                        $stmt->bindParam(':val2', $id_activite, PDO::PARAM_INT);

                                                        // Retrouver l'id correspondant au titre choisi
                                                        $id_titre = array_search($_POST['role'], $roles['intitules']);
                                                        $id_titre = $roles['ids'][$id_titre];

                                                        $stmt->bindParam(':val3', $id_titre, PDO::PARAM_INT);
                                                        $stmt->bindParam(':val4', $_POST['nbr_jours'], PDO::PARAM_INT);

                                                        if ($type_activite == 3) {
                                                            $stmt->bindParam(':val5', $_POST['nbr_copies'], PDO::PARAM_INT);
                                                        } else {
                                                            $stmt->bindValue(':val5', null, PDO::PARAM_NULL);
                                                        }

                                                        $resultat = $stmt->execute();

                                                        if (!$resultat) {
                                                            echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite lors de l'enregistrement des informations ! Rechargez la page pour tenter de le résoudre.</div>";
                                                            die(-1);
                                                        } else {
                                                            $message_succes = "<div class=\"alert alert-success mt-2\">Le participant a été enregistré avec succès !</div>";
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        if(isset($message_succes)){
                                            echo $message_succes;
                                        }
                                    }
                                }
                            }
                        ?>

                            <h4 class="my-4">Page d'ajout d'un participant</h4>

                            <form action="" method="post" enctype="multipart/form-data">

                                <!-- Informations générales -->

                                <fieldset>
                                    <legend>
                                        <h5>Informations générales</h5>
                                    </legend>
                                    <hr>

                                    <!-- Nom -->

                                    <div class="mb-2 row">
                                        <label for="nom" class="col-sm-3 col-form-label">Nom</label>
                                        <div class="col-sm-9">
                                            <input
                                                type="text"
                                                name="nom"
                                                maxlength="50"
                                                id="nom"
                                                class="form-control
                                                <?php if (isset($erreurs["nom"])) {
                                                    echo "is-invalid\" aria-describedby=\"nomAide";
                                                } ?>"
                                                placeholder="Entrez le nom"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["nom"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["nom"])) {
                                            ?>
                                                <div id="nomAide" class="form-text"><?php echo $erreurs["nom"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Prénoms -->

                                    <div class="mb-2 row">
                                        <label for="prenoms" class="col-sm-3 col-form-label">Prénoms</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="prenoms" maxlength="50" id="prenoms" class="form-control
                                            <?php if (isset($erreurs["prenoms"])) {
                                                echo "is-invalid\" aria-describedby=\"prenomsAide";
                                            } ?>"
                                                placeholder="Entrez le(s) prénom(s)"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["prenoms"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["prenoms"])) {
                                            ?>
                                                <div id="prenomsAide" class="form-text"><?php echo $erreurs["prenoms"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Matricule/IFU -->

                                    <div class="mb-2 row">
                                        <label for="matricule_ifu" class="col-form-label col-sm-3">Matricule/IFU</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="matricule_ifu" maxlength="50" id="matricule_ifu"
                                                class="form-control
                                                <?php if (isset($erreurs["matricule_ifu"])) {
                                                    echo "is-invalid\" aria-describedby=\"matricule_ifuAide";
                                                } ?>" placeholder="Entrez le matricule ou l'IFU"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["matricule_ifu"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["matricule_ifu"])) {
                                            ?>
                                                <div id="matricule_ifuAide" class="form-text"><?php echo $erreurs["matricule_ifu"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Date de naissance -->

                                    <div class="mb-2 row">
                                        <label for="date_naissance" class="col-form-label col-sm-3">Date de naissance</label>
                                        <div class="col-sm-9">
                                            <input type="date" name="date_naissance" id="date_naissance"
                                                class="form-control
                                                <?php if (isset($erreurs["date_naissance"])) {
                                                    echo "is-invalid\" aria-describedby=\"date_naissanceAide";
                                                } ?>"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["date_naissance"] . "\"";
                                                } ?>
                                                placeholder="bonjour">

                                            <?php if (isset($erreurs["date_naissance"])) {
                                            ?>
                                                <div id="date_naissanceAide" class="form-text"><?php echo $erreurs["date_naissance"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Lieu de naissance -->

                                    <div class="mb-2 row">
                                        <label for="lieu_naissance" class="col-form-label col-sm-3">Lieu de naissance </label>

                                        <div class="col-sm-9">
                                            <input type="text" name="lieu_naissance" id="lieu_naissance" class="form-control
                                            <?php if (isset($erreurs["lieu_naissance"])) {
                                                echo "is-invalid\" aria-describedby=\"lieu_naissanceAide";
                                            } ?>"
                                                placeholder="Entrez le lieu de naissance"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["lieu_naissance"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["lieu_naissance"])) {
                                            ?>
                                                <div id="lieu_naissanceAide" class="form-text"><?php echo $erreurs["lieu_naissance"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Diplôme -->

                                    <!-- <div class="mb-2 row">
                                        <label for="diplome" class="col-form-label col-sm-4">Diplôme le plus élevé</label>
                                        <div class="col-sm-8">
                                            <select name="diplome" id="diplome" class="form-select
                                            <?php if (isset($erreurs[" diplome"])) {
                                                echo "is-invalid\" aria-describedby=\"diplomeAide";
                                            } ?>">
                                                <option <?php if (!isset($_POST['diplome']) || !in_array($_POST['diplome'], $diplomes['intitules'])) {
                                                            echo "selected";
                                                        } ?>> Choisissez le diplôme...</option>
                                                <?php

                                                if (isset($diplomes)) {
                                                    foreach ($diplomes['intitules'] as $valeur) {
                                                ?>
                                                        <option <?php if (isset($erreurs) && $valeur == $_POST['diplome']) {
                                                                    echo "selected";
                                                                } ?> value="<?php echo $valeur; ?>"> <?php echo $valeur; ?></option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>

                                            <?php

                                            if (!isset($diplomes)) {
                                                echo "<div class=\"alert alert-danger mt-2\">La récupération des diplômes a rencontré un problème ! Rechargez la page pour le résoudre.</div>";
                                            }
                                            ?>

                                            <?php if (isset($erreurs["diplome"])) {
                                            ?>
                                                <div id="diplomeAide" class="form-text"><?php echo $erreurs["diplome"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div> -->

                                    <!-- Titre -->

                                    <!-- <div class="mb-2 row">
                                        <label for="role" class="col-form-label col-sm-3">Titre</label>
                                        <div class="col-sm-9">

                                            <select name="role" id="role" class="form-select
                                            <?php if (isset($erreurs["role"])) {
                                                echo "is-invalid\" aria-describedby=\"roleAide";
                                            } ?>">

                                                <option <?php if (!isset($_POST['role']) || !in_array($_POST['role'], $roles['intitules'])) {
                                                            echo "selected";
                                                        } ?>> Choisissez le diplôme...</option>
                                                <?php
                                                if (isset($roles)) {
                                                    foreach ($roles['intitules'] as $valeur) {
                                                ?>
                                                        <option <?php if (isset($erreurs) && $valeur == $_POST['role']) {
                                                                    echo "selected";
                                                                } ?> value="<?php echo $valeur; ?>"> <?php echo $valeur; ?>
                                                        </option>
                                                <?php
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <?php

                                            if (!isset($roles)) {
                                                echo "<div class=\"alert alert-danger mt-2\">La récupération des titres a rencontré un problème ! Rechargez la page pour le résoudre.</div>";
                                            }
                                            ?>

                                            <?php if (isset($erreurs["role"])) {
                                            ?>
                                                <div id="roleAide" class="form-text"><?php echo $erreurs["role"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div> -->
                                </fieldset>
                                <br>

                                <!-- Informations bancaires -->

                                <fieldset id="informations_bancaires">
                                    <legend>
                                        <h5>Informations bancaires</h5>
                                    </legend>
                                    <hr>

                                    <?php
                                    for ($i = 1; $i <= $nombre_comptes_bancaires; $i++) {
                                    ?>
                                        <?php
                                        if ($i > 1) {
                                        ?>
                                            <!-- Démarquation -->
                                            <div class="mb-2 row justify-content-end">
                                                <div class="col-sm-10">
                                                    <hr>
                                                </div>
                                            </div>
                                        <?php
                                        }
                                        ?>


                                        <div id="groupe_<?php echo $i; ?>">

                                            <!-- Banque (<?php echo $i; ?>) -->

                                            <div class="mb-2 row form-group">
                                                <label for="banque_<?php echo $i; ?>" class="col-form-label col-sm-3">Banque</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="banque_<?php echo $i; ?>" id="banque_<?php echo $i; ?>" class="form-control
                                                    <?php if (isset($erreurs["banque_$i"])) {
                                                        echo "is-invalid\" aria-describedby=\"banque$iAide";
                                                    } ?>"
                                                        placeholder="Indiquez la banque"
                                                        <?php if (isset($erreurs)) {
                                                            echo "value = \"" . $_POST["banque_$i"] . "\"";
                                                        } ?>>

                                                    <?php if (isset($erreurs["banque_$i"])) {
                                                    ?>
                                                        <div id="banque_<?php echo $i; ?>Aide" class="form-text"><?php echo $erreurs["banque_$i"][0] ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <!-- Numéro de compte (<?php echo $i; ?>) -->

                                            <div class="mb-2 row form-group">
                                                <label for="numero_compte_<?php echo $i; ?>" class="col-form-label col-sm-3">Numéro de compte</label>
                                                <div class="col-sm-9">
                                                    <input type="text" id="numero_compte_<?php echo $i; ?>" name="numero_compte_<?php echo $i; ?>" class="form-control
                                                    <?php if (isset($erreurs["numero_compte_$i"])) {
                                                        echo "is-invalid\" aria-describedby=\"numero_compte_$iAide";
                                                    } ?>"
                                                        placeholder="Entrez le numéro de compte"
                                                        <?php if (isset($erreurs)) {
                                                            echo "value = \"" . $_POST["numero_compte_$i"] . "\"";
                                                        } ?>>

                                                    <?php if (isset($erreurs["numero_compte_$i"])) {
                                                    ?>
                                                        <div id="numero_compte_<?php echo $i; ?>Aide" class="form-text"><?php echo $erreurs["numero_compte_$i"][0] ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>

                                            <!-- Copie PDF RIB (<?php echo $i; ?>) -->

                                            <div class="mb-2 row form-group">
                                                <label for="pdf_rib_<?php echo $i; ?>" class="col-form-label col-sm-3">Copie PDF du RIB</label>

                                                <div class="col-sm-9">
                                                    <input type="file" name="pdf_rib_<?php echo $i; ?>" size="50" id="pdf_rib_<?php echo $i; ?>" class="form-control
                                                    <?php if (isset($erreurs["pdf_rib_$i"])) {
                                                        echo "is-invalid\" aria-describedby=\"pdf_rib$iAide";
                                                    } ?>">

                                                    <?php if (isset($erreurs["pdf_rib_$i"])) {
                                                    ?>
                                                        <div id="pdf_rib_<?php echo $i; ?>Aide" class="form-text"><?php echo $erreurs["pdf_rib_$i"][0] ?></div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    }
                                    ?>
                                </fieldset>
                                <br>

                                <!-- Informations spécifiques à l'activité -->

                                <fieldset>
                                    <legend>
                                        <h5>Informations spécifiques à l'activité</h5>
                                    </legend>
                                    <hr>

                                    <!-- Nombre de jours -->

                                    <div class="mb-2 row">
                                        <label for="nbr_jours" class="col-sm-3 col-form-label">Nombre de jours</label>
                                        <div class="col-sm-9">
                                            <input type="number" name="nbr_jours" id="nbr_jours" class="form-control
                                            <?php if (isset($erreurs["nbr_jours"])) {
                                                echo "is-invalid\" aria-describedby=\"nbr_joursAide";
                                            } ?>" placeholder="Entrez le nombre de jours de travail"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["nbr_jours"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["nbr_jours"])) {
                                            ?>
                                                <div id="nbr_joursAide" class="form-text"><?php echo $erreurs["nbr_jours"][0] ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Nombre de copies -->

                                    <?php
                                    if ($type_activite == 3) {
                                    ?>
                                        <div class="mb-2 row">
                                            <label for="nbr_copies" class="col-sm-3 col-form-label">Nombre de copies</label>
                                            <div class="col-sm-9">
                                                <input type="number" name="nbr_copies" id="nbr_copies" class="form-control
                                            <?php if (isset($erreurs["nbr_copies"])) {
                                                echo "is-invalid\" aria-describedby=\"nbr_copiesAide";
                                            } ?>" placeholder="Entrez le nombre de copies corrigées"
                                                    <?php if (isset($erreurs)) {
                                                        echo "value = \"" . $_POST["nbr_copies"] . "\"";
                                                    } ?>>

                                                <?php if (isset($erreurs["nbr_copies"])) {
                                                ?>
                                                    <div id="nbr_copiesAide" class="form-text"><?php echo $erreurs["nbr_copies"][0] ?></div>
                                                <?php } ?>
                                            </div>
                                        </div>

                                    <?php
                                    } ?>

                                </fieldset>

                                <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $taille_admissible_fichiers_pdf; ?>">
                                <button type="submit" class="btn btn-primary mt-5 mb-4" name="enregistrer">Ajouter le participant</button><br>
                                <a href="index.php" class="btn btn-primary mb-4">Revenir à votre tableau de bord</a>
                            </form>

                            <!-- <script src="script.js"></script> -->

        <?php


                        }
                    }
                }
            }
        }
        ?>

        <script src="./bootstrap-5.3.5-dist/js/bootstrap.min.js"></script>
    </div>
</body>

</html>