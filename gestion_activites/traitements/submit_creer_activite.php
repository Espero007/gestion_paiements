<?php

if (isset($_GET['type_activite'])) {
    // On entame les validations en rapport avec le type de l'activité

    // Les validations vont prendre en compte les volets suivants :
    // 1- S'assurer que le champ est présent
    // 2- S'assurer que c'est un nombre
    // 3- S'assurer que ce nombre est compris entre 1 et 3
    // La fonction valider_valeur_numerique() va prendre en compte les deux premiers points et le troisième point sera géré ici

    if (valider_valeur_numerique('type_activite', $_GET)) {
        // Les deux premiers points sont ok, on vérifie alors le troisième point
        if ($_GET['type_activite'] == 1 || $_GET['type_activite'] == 2 || $_GET['type_activite'] == 3) {
            // Le type est valide
            $type_activite = $_GET['type_activite'];
            $recuperation_type_activite = true;
        } else {
            redirigerVersPageErreur('404', obtenirURLcourant());
        }
    } else {
        redirigerVersPageErreur('404', obtenirURLcourant());
    }
} else {
    $recuperation_type_activite = false;
}


if ($recuperation_type_activite) {
    // Si le type récupéré est valide on poursuit

    // $errors = [];
    $success = false;
    $id_user = $_SESSION['user_id'];
    $diplomes = [];
    $titres = [];
    $forfaires = [];

    // Initialisation des données pour le formulaire
    $data = [
        'nom' => '',
        'description' => '',
        'centre' => '',
        'premier_responsable' => '',
        'titre_responsable' => '',
        'organisateur' => '',
        'titre_organisateur' => '',
        'financier' => '',
        'titre_financier' => '',
        'note_generatrice' => '',
        'niveaux_diplome' => '',
        'titres_associes' => '',
        'taux_journalier' => '',
        'indemnite_forfaitaire' => '',
        'taux_taches' => '',
        'frais_deplacement_journalier' => '',
        'date_debut' => '',
        'date_fin' => '',
    ];

    // Récupérer les données et erreurs de la session si présentes

    $errors = $_SESSION['form_errors'] ?? [];
    $data = $_SESSION['form_data'] ?? $data;

    // Nettoyer les données de la session après utilisation
    unset($_SESSION['form_errors']);
    unset($_SESSION['form_data']);
    // unset($_SESSION['success_data']);


    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submitted'])) {
        foreach ($data as $key => $_) {
            $data[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : '';
        }

        // Vérification des doubles soumissions
        $submission_hash = md5($data['nom'] . $id_user . $data['date_debut']);
        if (isset($_SESSION['last_submission_hash']) && $_SESSION['last_submission_hash']['hash'] === $submission_hash && (time() - $_SESSION['last_submission_hash']['time'] < 10)) {
            $errors['duplicate'] = "Ce formulaire a déjà été soumis. Veuillez attendre un instant et réessayer.";
        } else {
            // Validations communes
            $common_fields = ['nom', 'description', 'centre', 'premier_responsable', 'organisateur', 'financier', 'niveaux_diplome', 'titres_associes', 'date_debut', 'date_fin'];
            foreach ($common_fields as $field) {
                if (empty($data[$field])) {
                    echo "Je suis ici";
                    $errors[$field] = "Le " . str_replace('_', ' ', $field) . " est requis.";
                }
            }

            // Validation des dates : date_fin >= date_debut
            if (!empty($data['date_debut']) && !empty($data['date_fin']) && $data['date_fin'] < $data['date_debut']) {
                $errors['date_fin'] = "La date de fin doit être égale ou postérieure à la date de début.";
            }

            // Validation de la note génératrice (obligatoire)
            if (!isset($_FILES['note_generatrice']) || $_FILES['note_generatrice']['error'] !== UPLOAD_ERR_OK) {
                $errors['note_generatrice'] = "La note génératrice est requise.";
            } else {
                $fileTmpPath = $_FILES['note_generatrice']['tmp_name'];
                $fileName = basename($_FILES['note_generatrice']['name']);
                $uploadFileDir = creer_dossiers_upload();
                $dest_path = $uploadFileDir . $fileName;
            }

            // Validation des titres associés
            if ($data['titres_associes'] !== '' && strpos($data['titres_associes'], ',,') !== false) {
                $errors['titres_associes'] = "Les titres contiennent des virgules consécutives non valides.";
            } elseif ($data['titres_associes'] !== '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['titres_associes'])) {
                $errors['titres_associes'] = "Les titres doivent être séparés par des virgules (ex. : Conference,Atelier).";
            } else {
                $titres = array_map('trim', explode(',', $data['titres_associes']));
                foreach ($titres as $titre) {
                    if (empty($titre)) {
                        $errors['titres_associes'] = "Chaque titre doit être une chaîne non vide.";
                    } elseif (!preg_match('/^[a-zA-Z]+$/', $titre)) {
                        $errors['titres_associes'] = "Chaque titre doit contenir uniquement des lettres (sans chiffres ni caractères spéciaux).";
                        break;
                    }
                }
            }

            // Validation des diplômes
            if ($data['niveaux_diplome'] !== '' && strpos($data['niveaux_diplome'], ',,') !== false) {
                $errors['niveaux_diplome'] = "Les niveaux contiennent des virgules consécutives non valides.";
            } elseif ($data['niveaux_diplome'] !== '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['niveaux_diplome'])) {
                $errors['niveaux_diplome'] = "Les niveaux doivent être séparés par des virgules (ex. : Licence,Master,Ingénieur).";
            } else {
                $diplomes = array_map('trim', explode(',', $data['niveaux_diplome']));
                foreach ($diplomes as $diplome) {
                    if (empty($diplome)) {
                        $errors['niveaux_diplome'] = "Chaque niveau doit être une chaîne non vide.";
                    } elseif (!preg_match('/^[\p{L}\s-]+$/u', $diplome) || preg_match('/[0-9]/', $diplome)) {
                        $errors['niveaux_diplome'] = "Chaque niveau doit contenir uniquement des lettres (accentuées ou non), espaces ou tirets, sans chiffres.";
                        break;
                    }
                }
            }

            // Validations spécifiques par type
            if (in_array($type_activite, [1, 2])) {
                if (empty($data['taux_journalier'])) {
                    $errors['taux_journalier'] = "Le taux journalier est requis.";
                } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $data['taux_journalier']) || $data['taux_journalier'] < 0) {
                    $errors['taux_journalier'] = "Le taux journalier doit être un montant en FCFA valide (ex. : 123.45, non négatif).";
                }
            }

            if (in_array($type_activite, [2, 3])) {
                if (empty($data['indemnite_forfaitaire'])) {
                    $errors['indemnite_forfaitaire'] = "L'indemnité forfaitaire est requise.";
                } elseif (strpos($data['indemnite_forfaitaire'], ',,') !== false) {
                    $errors['indemnite_forfaitaire'] = "Les indemnités contiennent des virgules consécutives non valides.";
                } elseif (!preg_match('/^[^,]+(,[^,]+)*$/', $data['indemnite_forfaitaire'])) {
                    $errors['indemnite_forfaitaire'] = "Les indemnités doivent être séparées par des virgules (ex. : 100.50,200.75).";
                } else {
                    $forfaires = array_map('trim', explode(',', $data['indemnite_forfaitaire']));
                    foreach ($forfaires as $forfaire) {
                        if (!preg_match('/^\d+(\.\d{1,2})?$/', $forfaire) || $forfaire < 0) {
                            $errors['indemnite_forfaitaire'] = "Chaque indemnité doit être un montant en FCFA valide (ex. : 123.45, non négatif).";
                            break;
                        }
                    }
                    if (count($titres) !== count($forfaires)) {
                        $errors['titres_associes'] = $errors['indemnite_forfaitaire'] = "Le nombre d'indemnités doit être égal au nombre de titres.";
                    }
                }
            }

            if ($type_activite === '3') {
                if (empty($data['taux_taches'])) {
                    $errors['taux_taches'] = "Le taux par tâche est requis.";
                } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $data['taux_taches']) || $data['taux_taches'] < 0) {
                    $errors['taux_taches'] = "Le taux par tâche doit être un montant en FCFA valide (ex. : 123.45, non négatif).";
                }

                if (empty($data['frais_deplacement_journalier'])) {
                    $errors['frais_deplacement_journalier'] = "Les frais de déplacement sont requis.";
                } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $data['frais_deplacement_journalier']) || $data['frais_deplacement_journalier'] < 0) {
                    $errors['frais_deplacement_journalier'] = "Les frais de déplacement doivent être un montant en FCFA valide (ex. : 123.45, non négatif).";
                }
            }

            // Début des insertions en bdd s'il n'y a pas d'erreurs
            if (empty($errors)) {
                try {
                    // Enregistrement de la note génératrice
                    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                        $errors['note_generatrice'] = "Échec du déplacement du fichier. Vérifiez les permissions.";
                    } else {
                        // Le fichier a bien été enregistré
                        // Insérer la note génératrice dans la table fichiers
                        $sql = 'INSERT INTO fichiers (chemin_acces, nom_original, date_upload, type_fichier) 
                    VALUES (:chemin_acces, :nom_original, :date_upload, :type_fichier)';
                        $stmt = $bdd->prepare($sql);
                        $stmt->execute([
                            'chemin_acces' => $dest_path,
                            'nom_original' => $fileName,
                            'date_upload' => date('Y-m-d H:i:s'),
                            'type_fichier' => 'note_generatrice'
                        ]);
                        $id_note_generatrice = $bdd->lastInsertId();

                        // Insérer l'activité
                        $sql = 'INSERT INTO activites(type_activite, id_user, nom, description, date_debut, date_fin, centre, premier_responsable, titre_responsable, organisateur, titre_organisateur, financier, titre_financier, id_note_generatrice, taux_journalier, taux_taches, frais_deplacement_journalier)
                    VALUES (:type_activite, :id_user, :nom, :description, :periode_debut, :periode_fin, :centre, :premier_responsable, :titre_responsable, :organisateur, :titre_organisateur, :financier, :titre_financier, :id_note_generatrice, :taux_journalier, :taux_taches, :frais_deplacement_journalier)';
                        $stmt = $bdd->prepare($sql);
                        $stmt->execute([
                            'type_activite' => $type_activite,
                            'id_user' => $id_user,
                            'nom' => $data['nom'],
                            'description' => $data['description'],
                            'periode_debut' => $data['date_debut'],
                            'periode_fin' => $data['date_fin'],
                            'centre' => $data['centre'],
                            'premier_responsable' => $data['premier_responsable'],
                            'titre_responsable' => $data['titre_responsable'] ?: null,
                            'organisateur' => $data['organisateur'],
                            'titre_organisateur' => $data['titre_organisateur'] ?: null,
                            'financier' => $data['financier'],
                            'titre_financier' => $data['titre_financier'] ?: null,
                            'id_note_generatrice' => $id_note_generatrice,
                            'taux_journalier' => in_array($type_activite, ['1', '2']) ? $data['taux_journalier'] : null,
                            'taux_taches' => $type_activite === '3' ? $data['taux_taches'] : null,
                            'frais_deplacement_journalier' => $type_activite === '3' ? $data['frais_deplacement_journalier'] : null
                        ]);

                        $last_id = $bdd->lastInsertId();

                        // Insertion des diplômes
                        $sql_diplome = 'INSERT INTO diplomes(id_activite, nom) VALUES (:id_activite, :nom)';
                        $stmt_diplome = $bdd->prepare($sql_diplome);
                        foreach ($diplomes as $diplome) {
                            $stmt_diplome->execute([
                                'id_activite' => $last_id,
                                'nom' => $diplome
                            ]);
                        }

                        // Insertion des titres associés
                        $sql_titre = 'INSERT INTO titres(id_activite, nom, indemnite_forfaitaire) VALUES (:id_activite, :nom, :indemnite_forfaitaire)';
                        $stmt_titre = $bdd->prepare($sql_titre);
                        if ($type_activite === 1) {
                            foreach ($titres as $titre) {
                                $stmt_titre->execute([
                                    'id_activite' => $last_id,
                                    'nom' => $titre,
                                    'indemnite_forfaitaire' => null
                                ]);
                            }
                        } elseif (in_array($type_activite, [2, 3])) {
                            foreach (array_combine($titres, $forfaires) as $titre => $forfaire) {
                                $stmt_titre->execute([
                                    'id_activite' => $last_id,
                                    'nom' => $titre,
                                    'indemnite_forfaitaire' => $forfaire
                                ]);
                            }
                        }

                        // Stocker le hash de la soumission
                        $_SESSION['last_submission_hash'] = [
                            'hash' => $submission_hash,
                            'time' => time()
                        ];

                        $success = true;
                        // $_SESSION['success'] = true;

                        // Stocker les données pour le message de succès
                        // $data['note_generatrice'] = $fileName; // Pour affichage
                        // $_SESSION['success_data'] = $data; // On a plus besoin vu qu'on affiche plus les informations en cas de succès
                        // Rediriger pour éviter les doubles insertions
                        // header('Location: /gestion_activites/creer_activite.php?success=1'); // On évite la redirection car l'utilisateur pourrait vouloir continuer les enregistrements avec le même type d'activité
                        // exit;
                    }
                } catch (PDOException $e) {
                    $errors['database'] = "Une erreur s'est produite. Veuillez réessayer.";
                    die('Erreur : ' . $e->getMessage());
                }
            } else {
                // Il y a des erreurs
                // Stocker les données et erreurs dans la session pour affichage
                $_SESSION['form_data'] = $data;
                $_SESSION['form_errors'] = $errors;
            }
        }
    }
}