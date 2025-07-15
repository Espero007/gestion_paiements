<?php
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');


$errors = [];
$success = false;
$id_user = $_SESSION['user_id'];
$diplomes = [];
$titres = [];
$forfaires = [];


// Vérifier si l'ID de l'activité est fourni
if (!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
    header('Location:' . $_SESSION["previous_url"]);
    exit;
   
}
$activity_id = $_POST['id'];


// Vérifier si l'activité existe et appartient à l'utilisateur
try {
    $sql = 'SELECT type_activite FROM activites WHERE id = :id AND id_user = :id_user';
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['id' => $activity_id, 'id_user' => $id_user]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$activity) {
        $_SESSION['form_errors'] = ['database' => "Activité non trouvée ou vous n'avez pas les permissions pour la modifier."];
        header('Location:' . $_SESSION["previous_url"]);
        exit;
    }

    $type_activite = $activity['type_activite'];
    //$current_id_note_generatrice = $activity['id_note_generatrice'];
} catch (PDOException $e) {
    $_SESSION['form_errors'] = ['database' => "Erreur lors de la vérification de l'activité. Veuillez réessayer."];
    header('Location:' . $_SESSION["previous_url"]);
    exit;
}

// Initialisation des données à vide
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

// Champs à afficher dans le message de succès par type
$fields_to_display = [
    '1' => ['nom', 'description', 'centre', 'timbre' , 'reference' , 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier','niveaux_diplome', 'titres_associes', 'taux_journalier', 'date_debut', 'date_fin'],
    '2' => ['nom', 'description', 'centre', 'timbre' , 'reference' ,'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier','niveaux_diplome', 'titres_associes', 'taux_journalier', 'indemnite_forfaitaire', 'date_debut', 'date_fin'],
    '3' => ['nom', 'description', 'centre', 'timbre' , 'reference' ,'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier','niveaux_diplome', 'titres_associes', 'indemnite_forfaitaire', 'taux_taches', 'frais_deplacement_journalier', 'date_debut', 'date_fin']
];

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submitted'])) {
    foreach ($data as $key => $_) {
        $data[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }


    // Vérification des doubles soumissions
    $submission_hash = md5($data['nom'] . $id_user . $data['date_debut'] . $activity_id);
    if (isset($_SESSION['last_submission_hash']) && $_SESSION['last_submission_hash']['hash'] === $submission_hash && (time() - $_SESSION['last_submission_hash']['time'] < 10)) {
        $errors['duplicate'] = "Ce formulaire a déjà été soumis. Veuillez attendre un instant et réessayer.";
    } else {
        ### Validations communes
        $champs_texts = ['nom', 'timbre', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'reference'];
        $common_fields = ['nom', 'description', 'centre', 'premier_responsable', 'organisateur', 'financier', 'niveaux_diplome', 'titres_associes', 'date_debut', 'date_fin', 'timbre' , 'reference' ,'titre_responsable' ,'titre_organisateur','titre_financier'];
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
        }


        // Validation des dates : date_fin >= date_debut
        if (!empty($data['date_debut']) && !empty($data['date_fin']) && $data['date_fin'] < $data['date_debut']) {
            $errors['date_fin'] = "La date de fin doit être égale ou postérieure à la date de début.";
        }

        /*
        // Validation de note_generatrice (facultatif, mais si fourni, doit être valide)
        $new_id_note_generatrice = $current_id_note_generatrice;
        $old_file_path = null;
        if (isset($_FILES['note_generatrice']) && $_FILES['note_generatrice']['error'] === UPLOAD_ERR_OK) {

            // $extension_fichier = strtolower(pathinfo($_FILES['note_generatrice']['name'], PATHINFO_EXTENSION));
            // $extensions_autorisees = ['pdf'];

            // if(!in_array($extension_fichier, $extensions_autorisees)){
            //     $errors['note_generatrice'] = "Le fichier attendu est de type PDF";
            // }else{
            // }

            $fileTmpPath = $_FILES['note_generatrice']['tmp_name'];
            $fileName = basename($_FILES['note_generatrice']['name']);
            $uploadFileDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $fileName;
            if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                $errors['note_generatrice'] = "Échec du déplacement du fichier. Vérifiez les permissions.";
            } else {
                // Récupérer l'ancien chemin du fichier pour suppression
                if ($current_id_note_generatrice) {
                    $sql = 'SELECT chemin_acces FROM fichiers WHERE id_fichier = :id_fichier';
                    $stmt = $bdd->prepare($sql);
                    $stmt->execute(['id_fichier' => $current_id_note_generatrice]);
                    $old_file = $stmt->fetch(PDO::FETCH_ASSOC);
                    $old_file_path = $old_file['chemin_acces'] ?? null;
                }
            }
        } elseif (isset($_FILES['note_generatrice']) && $_FILES['note_generatrice']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors['note_generatrice'] = "Erreur lors de l'upload du fichier.";
        }
        */

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
                } elseif (preg_match('/[^\p{L} -]/u', $titre)) {
                    $errors['titres_associes'] = "Chaque titre doit contenir uniquement des lettres (sans chiffres ni caractères spéciaux).";
                    break;
                }
            }
        }

        // Validation des diplômes
        if ($data['niveaux_diplome'] !== '' && strpos($data['niveaux_diplome'], ',,') !== false) {
            $errors['niveaux_diplome'] = "Les diplômes contiennent des virgules consécutives non valides.";
        } elseif ($data['niveaux_diplome'] !== '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['niveaux_diplome'])) {
            $errors['niveaux_diplome'] = "Les diplômes doivent être séparés par des virgules (ex. : Licence,Master,Ingénieur).";
        } else {
            $diplomes = array_map('trim', explode(',', $data['niveaux_diplome']));
            foreach ($diplomes as $diplome) {
                if (empty($diplome)) {
                    $errors['niveaux_diplome'] = "Chaque diplôme doit être une chaîne non vide.";
                } elseif (!preg_match('/^[\p{L}\s-]+$/u', $diplome) || preg_match('/[0-9]/', $diplome)) {
                    $errors['niveaux_diplome'] = "Chaque diplôme doit contenir uniquement des lettres (accentuées ou non), espaces ou tirets, sans chiffres.";
                    break;
                }
            }
        }

        // Validations spécifiques par type
        if (in_array($type_activite, ['1', '2'])) {
            if (empty($data['taux_journalier'])) {
                $errors['taux_journalier'] = "Le taux journalier est requis.";
            } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $data['taux_journalier']) || $data['taux_journalier'] < 0) {
                $errors['taux_journalier'] = "Le taux journalier doit être un montant en FCFA valide (ex. : 123.45, non négatif).";
            }
        }

        if (in_array($type_activite, ['2', '3'])) {
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

        if (empty($errors)) {

            // On vérifie si une activité exactement identique n'est pas déjà présente en bdd
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
                $stmt .= 'AND taux_journalier=:val15 ';
            } else {
                $stmt .= 'AND taux_journalier IS NULL ';
            }

            if ($type_activite == 3) {
                $stmt .= 'AND taux_taches=:val16 AND frais_deplacement_journalier=:val17';
            } else {
                $stmt .= 'AND taux_taches IS NULL AND frais_deplacement_journalier IS NULL';
            }

            $stmt .= ' AND id !=' . $activity_id;
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
                $_SESSION['erreur_modifier_infos'] = 'Il semble que vous avez déjà créé une activité avec les mêmes informations.';
                header('Location: ../gerer_activite.php?id=' . $activity_id);
                exit;
            } else {

                try {

                    /*
                    // Si un nouveau fichier est uploadé, insérer dans fichiers
                    if (isset($fileName) && isset($dest_path)) {
                        $sql = 'INSERT INTO fichiers (chemin_acces, nom_original, date_upload, type_fichier) 
                            VALUES (:chemin_acces, :nom_original, :date_upload, :type_fichier)';
                        $stmt = $bdd->prepare($sql);
                        $stmt->execute([
                            'chemin_acces' => $dest_path,
                            'nom_original' => $fileName,
                            'date_upload' => date('Y-m-d H:i:s'),
                            'type_fichier' => 'note_generatrice'
                        ]);
                        $new_id_note_generatrice = $bdd->lastInsertId();

                        // Supprimer l'ancien fichier physique
                        if ($old_file_path && file_exists($old_file_path)) {
                            unlink($old_file_path);
                        }
                    }
                    */

                    // Mettre à jour l'activité
                    $sql = 'UPDATE activites SET 
                        nom = :nom, 
                        description = :description, 
                        date_debut = :date_debut, 
                        date_fin = :date_fin, 
                        centre = :centre,
                        timbre = :timbre,
                        reference = :reference,   
                        premier_responsable = :premier_responsable, 
                        titre_responsable = :titre_responsable, 
                        organisateur = :organisateur, 
                        titre_organisateur = :titre_organisateur, 
                        financier = :financier, 
                        titre_financier = :titre_financier, 
                        taux_journalier = :taux_journalier, 
                        taux_taches = :taux_taches, 
                        frais_deplacement_journalier = :frais_deplacement_journalier 
                        WHERE id = :id AND id_user = :id_user';
                    $stmt = $bdd->prepare($sql);
                    $stmt->execute([
                        'nom' => $data['nom'],
                        'description' => $data['description'],
                        'date_debut' => $data['date_debut'],
                        'date_fin' => $data['date_fin'],
                        'centre' => $data['centre'],
                        'timbre' => mb_strtoupper($data['timbre'], 'UTF-8'),
                        'reference' => mb_strtoupper($data['reference'], 'UTF-8'),
                        'premier_responsable' => $data['premier_responsable'],
                        'titre_responsable' => $data['titre_responsable'] ,
                        'organisateur' => $data['organisateur'],
                        'titre_organisateur' => $data['titre_organisateur'] ,
                        'financier' => $data['financier'],
                        'titre_financier' => $data['titre_financier'] ,
                        'taux_journalier' => in_array($type_activite, ['1', '2']) ? $data['taux_journalier'] : null,
                        'taux_taches' => $type_activite === 3 ? $data['taux_taches'] : null,
                        'frais_deplacement_journalier' => $type_activite === 3 ? $data['frais_deplacement_journalier'] : null,
                        'id' => $activity_id,
                        'id_user' => $id_user
                    ]);

     // Mettre à jour les diplômes
        $diplomes_str = implode(',', $diplomes);
        $sql_diplome = 'UPDATE diplomes SET noms = :noms WHERE id_activite = :id_activite';
        $stmt_diplome = $bdd->prepare($sql_diplome);
        $stmt_diplome->execute([
            'id_activite' => $activity_id,
            'noms' => $diplomes_str
            ]);

        // Mettre à jour les titres
        $sql_titre = 'UPDATE titres SET nom = :nom, indemnite_forfaitaire = :indemnite_forfaitaire WHERE id_activite = :id_activite AND nom = :old_nom';
        $stmt_titre = $bdd->prepare($sql_titre);

        if ($type_activite == '1') {
            foreach ($titres as $index => $titre) {
                // Supposer que les titres sont dans le même ordre que les enregistrements existants
                $stmt_titre->execute([
                    'id_activite' => $activity_id,
                    'nom' => $titre,
                    'indemnite_forfaitaire' => null,
                    'old_nom' => $titres[$index] // Utiliser le même titre comme clé de correspondance
                ]);
            }
        } elseif (in_array($type_activite, ['2', '3'])) {
            foreach (array_combine($titres, $forfaires) as $titre => $forfaire) {
                $stmt_titre->execute([
                    'id_activite' => $activity_id,
                    'nom' => $titre,
                    'indemnite_forfaitaire' => $forfaire,
                    'old_nom' => $titre // Utiliser le même titre comme clé de correspondance
                ]);
            }
        }

                    // Stocker le hash de la soumission
                    $_SESSION['last_submission_hash'] = [
                        'hash' => $submission_hash,
                        'time' => time()
                    ];

                    // Pour afficher message de succès
                    $success = true;
                    $_POST = []; // On vide la superglobale

                    // Stocker les données pour le message de succès
                    //$data['note_generatrice'] = isset($fileName) ? $fileName : ($data['note_generatrice'] ?: 'Aucun fichier');
                    //$_SESSION['success_data'] = $data;
                    // Rediriger pour éviter les doubles soumissions
                    if ($success) {
                        $_SESSION['success'] = 'Vos modifications ont été enregistrées avec succès !';
                        header('Location: ../gerer_activite.php?id=' . $activity_id);
                        exit;
                    }
                } catch (PDOException $e) {
                    die('Erreur : ' . $e->getMessage());
                    $errors['database'] = "Une erreur s'est produite. Veuillez réessayer.";
                    // Supprimer le nouveau fichier si l'insertion échoue
                    if (isset($dest_path) && file_exists($dest_path)) {
                        unlink($dest_path);
                    }
                }
            }
        }
    }


    // Si erreurs, stocker les données et erreurs dans la session pour affichage
    if (!empty($errors)) {
        $_SESSION['form_data'] = $data;
        $_SESSION['form_errors'] = $errors;

        header('Location: ../modifier_infos.php?id=' . urlencode($activity_id));
        exit;
    }
}
