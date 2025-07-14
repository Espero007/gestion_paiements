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
    $forfaits = [];

    // Initialisation des données pour le formulaire
    $data = [
        'nom' => '',
        'timbre' => '',
        'reference' => '',
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

    $errors =  [];


    // Vérifier si le formulaire a été soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submitted'])) {
        // Un simple traitement des valeurs qu'on a reçu
        foreach ($data as $key => $_) {
            $data[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : '';
            if($key=='timbre' || $key == 'reference'){
                $data[$key] = isset($_POST[$key]) ? mb_strtoupper(trim($_POST[$key]), 'UTF-8') : '';
            }
        }

        ### Validations communes

        // Champs à valider
        $champs_texts = ['nom', 'timbre', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'reference'];
        $common_fields = array_merge($champs_texts, ['niveaux_diplome', 'titres_associes', 'date_debut', 'date_fin']);

        foreach ($common_fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = 'Veuillez remplir ce champ';
            }
        }

        // Validations sur les valeurs textuelles

        foreach ($champs_texts as $champ) {
            /**
             * Quelques explications sur les regex utilisés
             * if (!preg_match('/^[\p{L} \-\']+$/u', $data[$champ]))
             * Ce qu'elle fait :
             * Elle vérifie que toute la chaîne (^ début, $ fin) est composée uniquement :
             * de lettres Unicode (\p{L})
             * d'espaces ( )
             * de tirets (-)
             * d'apostsrophes (')
             * Ce qu'elle accepte :
             * "Jean-Paul"
             * "Marie Claire"
             * "Éléonore"
             * Ce qu'elle refuse :
             * "Jean123" (chiffres interdits)
             * "Jean!" (caractères spéciaux interdits)
             * "Paul_" (underscore interdit)
             * "" (chaîne vide si + est utilisé, car il faut au moins un caractère valide)

             * Résumé :
             * Cette version vérifie si la chaîne entière est correcte.
             * Elle est stricte et positive : on valide la chaîne si elle correspond entièrement au motif.
             */

            if ($champ != 'timbre' && $champ != 'centre' && $champ != 'reference') {
                if (!preg_match('/^[\p{L} \-\']+$/u', $data[$champ])) {
                    if (!isset($errors[$champ])) {
                        $errors[$champ] = "Ce champ contient des caractères non valides !";
                    }
                }
            } elseif ($champ == 'centre') {
                // Ce regex par contre accepte, en plus de ce que le regex précédern accepte, des chiffres par le '\p{N}'
                if (!preg_match('/^[\p{L}\p{N} \-\']+$/u', $data[$champ])) {
                    if (!isset($errors[$champ]))
                        $errors[$champ] = "Ce champ contient des caractères non valides !";
                }
            } elseif ($champ == 'timbre') {
                /**
                 * Explications du regex utilisé
                 * \/? : 0 ou 1 slash au début (optionnel)
                 * \/? : 0 ou 1 slash au début (optionnel)
                 * [A-Za-z0-9]+ : un segment composé uniquement de lettres ASCII non accentuées et chiffres, au moins un caractère
                 * (\/[A-Za-z0-9]+)+ : au moins un slash suivi d’un segment similaire (donc au moins 2 segments)
                 * ^...$ : la chaîne entière doit correspondre
                 * Pas d'espaces ni autres caractères autorisés

                 * Exemples valides :
                 * DEG/MAS
                 * a/b
                 * ALPHA/BETA/GAMMA
                 * Jean123/Paul456
                 * ABC/DEF123/GHI456

                 * Exemples refusés :
                 * A	(un seul segment)
                 * mot	(un seul mot)
                 * /alpha	(commence par /)
                 * alpha/	(finit par /)
                 * alpha//beta	(segment vide)
                 * alpha/ be ta	(contient un espace)
                 * Élodie/Jean
                 */

                if (!preg_match('/^\/[A-Za-z0-9]+(\/[A-Za-z0-9]+)+$/', $data[$champ])) {
                    if (!isset($errors[$champ])) {
                        $errors[$champ] = "La valeur que vous avez indiquée ne respecte pas le format attendu";
                    }
                }
            }elseif($champ == 'reference'){
                if (!preg_match('/^[A-Za-z0-9]+(\/[A-Za-z0-9]+)+$/', $data[$champ])) {
                    if (!isset($errors[$champ])) {
                        $errors[$champ] = "La valeur que vous avez indiquée ne respecte pas le format attendu";
                    }
                }
        }

        // Validation des dates : date_fin >= date_debut
        if (!empty($data['date_debut']) && !empty($data['date_fin']) && $data['date_fin'] < $data['date_debut']) {
            $errors['date_fin'] = "La date de fin doit être égale ou postérieure à la date de début.";
        }

        // Validation des titres associés
        if ($data['titres_associes'] !== '' && strpos($data['titres_associes'], ',,') !== false) {
            $errors['titres_associes'] = "Les titres contiennent des virgules consécutives non valides.";
        } elseif ($data['titres_associes'] !== '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['titres_associes'])) {
            $errors['titres_associes'] = "Les titres doivent être séparés par des virgules (ex. : Conférence,Atelier).";
        } elseif (!empty($data['titres_associes'])) {
            $titres = array_map('trim', explode(',', $data['titres_associes']));
            foreach ($titres as $titre) {
                if (empty($titre)) {
                    $errors['titres_associes'] = "Chaque titre doit être une chaîne non vide.";
                } elseif (preg_match('/[^\p{L} -]/u', $titre)) {
                    $errors['titres_associes'] = "Chaque titre doit être une chaîne de caractères alphabétiques.";
                    break;
                }
            }
        }

        // Validation des diplômes
        if ($data['niveaux_diplome'] !== '' && strpos($data['niveaux_diplome'], ',,') !== false) {
            $errors['niveaux_diplome'] = "Les niveaux contiennent des virgules consécutives non valides.";
        } elseif ($data['niveaux_diplome'] !== '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['niveaux_diplome'])) {
            $errors['niveaux_diplome'] = "Les niveaux doivent être séparés par des virgules (ex. : Licence,Master,Ingénieur).";
        } elseif (!empty($data['niveaux_diplome'])) {
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
                $errors['taux_journalier'] = "Veuillez remplir ce champ";
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
                $forfaits = array_map('trim', explode(',', $data['indemnite_forfaitaire']));
                foreach ($forfaits as $forfait) {
                    if (!preg_match('/^\d+(\.\d{1,2})?$/', $forfait) || $forfait < 0) {
                        $errors['indemnite_forfaitaire'] = "Chaque indemnité doit être un montant en FCFA valide (ex. : 123.45, non négatif).";
                        break;
                    }
                }
                if (count($titres) !== count($forfaits)) {
                    $errors['titres_associes'] = $errors['indemnite_forfaitaire'] = "Le nombre d'indemnités doit être égal au nombre de titres.";
                }
            }
        }

        if ($type_activite === 3) {
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

        # Début des insertions en bdd s'il n'y a pas d'erreurs

        if (empty($errors)) {

            // On vérifie si une activité exactement identique n'est pas déjà présente en bdd
            $doublon = false;

            // Préparation de la requête

            $stmt = '
            SELECT 
            id 
            FROM activites 
            WHERE type_activite=:val1 
            AND id_user=:val2 
            AND nom=:val3 
            AND description=:val4 
            AND date_debut=:val5 
            AND date_fin=:val6 
            AND centre=:val7 
            AND premier_responsable=:val8 
            AND titre_responsable=:val9 
            AND organisateur=:val10 
            AND titre_organisateur=:val11 
            AND financier=:val12 
            AND titre_financier=:val13 
            AND timbre=:val14 
            AND reference=:reference ';

            if (in_array($type_activite, [1, 2])) {
                $stmt .= ' AND taux_journalier=:val15 ';
            } else {
                $stmt .= ' AND taux_journalier IS NULL ';
            }

            if ($type_activite == 3) {
                $stmt .= ' AND taux_taches=:val16 AND frais_deplacement_journalier=:val17';
            } else {
                $stmt .= ' AND taux_taches IS NULL AND frais_deplacement_journalier IS NULL';
            }
            $sql = $bdd->prepare($stmt);

            $sql->bindParam('val1', $type_activite, PDO::PARAM_INT);
            $sql->bindParam('val2', $id_user, PDO::PARAM_INT);
            $sql->bindParam('val3', $data['nom']);
            $sql->bindParam('val4', $data['description']);
            $sql->bindParam('val5', $data['date_debut']);
            $sql->bindParam('val6', $data['date_fin']);
            $sql->bindParam('val7', $data['centre']);
            $sql->bindParam('val8', $data['premier_responsable']);
            $sql->bindParam('val9', $data['titre_responsable']);
            $sql->bindParam('val10', $data['organisateur']);
            $sql->bindParam('val11', $data['titre_organisateur']);
            $sql->bindParam('val12', $data['financier']);
            $sql->bindParam('val13', $data['titre_financier']);
            $sql->bindParam('val14', $data['timbre']);
            $sql->bindParam('reference', $data['reference']);
            // Taux journalier
            if (in_array($type_activite, [1, 2])) {
                $sql->bindParam('val15', $data['taux_journalier'], PDO::PARAM_INT);
            }
            // Taux tâches et frais de déplacement journalier
            if ($type_activite == 3) {
                $sql->bindParam('val16', $data['taux_taches']);
                $sql->bindParam('val17', $data['frais_deplacement_journalier']);
            }
            $sql->execute();

            if ($sql->rowCount() != 0) {
                $doublon = true;
            } else {
                try {
                    // Insérer l'activité
                    $sql = '
                        INSERT INTO activites(type_activite, id_user, nom, description, date_debut, date_fin, centre, premier_responsable, titre_responsable, organisateur, titre_organisateur, financier, titre_financier, timbre, taux_journalier, taux_taches, frais_deplacement_journalier, reference)
                        VALUES (:type_activite, :id_user, :nom, :description, :periode_debut, :periode_fin, :centre, :premier_responsable, :titre_responsable, :organisateur, :titre_organisateur, :financier, :titre_financier, :timbre, :taux_journalier, :taux_taches, :frais_deplacement_journalier, :reference)';

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
                        'titre_responsable' => $data['titre_responsable'],
                        'organisateur' => $data['organisateur'],
                        'titre_organisateur' => $data['titre_organisateur'],
                        'financier' => $data['financier'],
                        'titre_financier' => $data['titre_financier'],
                        'timbre' => mb_strtoupper($data['timbre'], 'UTF-8'),
                        'taux_journalier' => in_array($type_activite, ['1', '2']) ? $data['taux_journalier'] : null,
                        'taux_taches' => $type_activite == 3 ? $data['taux_taches'] : null,
                        'frais_deplacement_journalier' => $type_activite == 3 ? $data['frais_deplacement_journalier'] : null,
                        'reference' => mb_strtoupper($data['reference'], 'UTF-8')
                    ]);

                    $id_activite = $bdd->lastInsertId();

                    // Insertion des diplômes
                    $sql = 'INSERT INTO diplomes(noms, id_activite) VALUES (:diplomes, :id_activite)';
                    $stmt = $bdd->prepare($sql);

                    $stmt->execute([
                        'id_activite' => $id_activite,
                        'diplomes' => $_POST['niveaux_diplome']
                    ]);

                    // Insertion des titres associés
                    $sql = 'INSERT INTO titres(id_activite, nom, indemnite_forfaitaire) VALUES (:id_activite, :nom, :indemnite_forfaitaire)';
                    $stmt = $bdd->prepare($sql);
                    if ($type_activite == 1) {
                        foreach ($titres as $titre) {
                            $stmt->execute([
                                'id_activite' => $id_activite,
                                'nom' => $titre,
                                'indemnite_forfaitaire' => null
                            ]);
                        }
                    } elseif (in_array($type_activite, [2, 3])) {
                        foreach (array_combine($titres, $forfaits) as $titre => $forfait) {
                            $stmt->execute([
                                'id_activite' => $id_activite,
                                'nom' => $titre,
                                'indemnite_forfaitaire' => $forfait
                            ]);
                        }
                    }

                    // Message de succès
                    $_POST = []; // On vide la superglobale
                    $_SESSION['success'] = 'Votre activité a été créée avec succès. Pensez à y <a href="/gestion_participants/lier_participant_activite.php?id_activite=' . $id_activite . '">associer des participants</a>';
                    header('Location:gerer_activite.php?id=' . $id_activite);
                    exit;
                } catch (PDOException $e) {
                    $errors['database'] = "Une erreur s'est produite. Veuillez réessayer.";
                    die('Erreur : ' . $e->getMessage());
                }
            }
        }
    }
}
