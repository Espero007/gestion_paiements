<?php
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

$errors = [];
$success = false;
$id_user = $_SESSION['user_id'];
$diplomes = [];
$titres = [];
$forfaires = [];

// Vérifier si l'ID de l'activité est fourni
if (!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['form_errors'] = ['id' => "ID de l'activité non valide."];
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
} catch (PDOException $e) {
    $_SESSION['form_errors'] = ['database' => "Erreur lors de la vérification de l'activité : " . $e->getMessage()];
    header('Location:' . $_SESSION["previous_url"]);
    exit;
}

// Récupérer les anciens titres de l'activité
try {
    $stmt = $bdd->prepare('SELECT id_titre, nom, indemnite_forfaitaire FROM titres WHERE id_activite = :id_activite');
    $stmt->execute(['id_activite' => $activity_id]);
    $anciens_titres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors['titres'] = "Erreur lors de la récupération des titres existants : " . $e->getMessage();
}

// Initialisation des données
$data = [
    'nom' => '',
    'timbre' => '',
    'reference' =>'',
    'description' => '',
    'centre' => '',
    'premier_responsable' => '',
    'titre_responsable' => '',
    'organisateur' => '',
    'titre_organisateur' => '',
    'financier' => '',
    'titre_financier' => '',
    'note_generatrice' => '',
    'taux_journalier' => '',
    'taux_taches' => '',
    'frais_deplacement_journalier' => '',
    'date_debut' => '',
    'date_fin' => '',
    'mode_payement' => '',
];

// Champs à afficher dans le message de succès par type
$fields_to_display = [
    '1' => ['nom', 'description', 'centre', 'timbre', 'reference', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier',  'titres_associes', 'taux_journalier', 'date_debut', 'date_fin','mode_payement'],
    '2' => ['nom', 'description', 'centre', 'timbre', 'reference', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier',  'titres_associes', 'taux_journalier', 'indemnite_forfaitaire', 'date_debut', 'date_fin','mode_payement'],
    '3' => ['nom', 'description', 'centre', 'timbre', 'reference', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'titres_associes', 'indemnite_forfaitaire', 'taux_taches', 'frais_deplacement_journalier', 'date_debut', 'date_fin','mode_payement']
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
        $common_fields = ['nom', 'description', 'centre', 'premier_responsable', 'organisateur', 'financier', 'date_debut', 'date_fin', 'timbre', 'reference', 'titre_responsable', 'titre_organisateur', 'titre_financier'];
        foreach ($common_fields as $field) {
            if (empty($data[$field])) {
                $errors[$field] = 'Veuillez remplir ce champ';
            }
        }

        // Validation du mode de payement (champ radio, doit être 0 ou 1)
        if ($data['mode_payement'] === '') {
            $errors['mode_payement'] = "Veuillez sélectionner le mode de payement.";
        } elseif (!in_array($data['mode_payement'], ['0', '1'], true)) {
            $errors['mode_payement'] = "Le mode de payement sélectionné n'est pas valide.";
        }

        // Validations sur les valeurs textuelles
        foreach ($champs_texts as $champ) {
            if ($champ != 'timbre' && $champ != 'reference' && $champ != 'description') {
                if (!preg_match('/^[\p{L}\p{N} \-\'\/]+$/u', $data[$champ])) {
                    if (!isset($errors[$champ])) {
                        $errors[$champ] = "Ce champ contient des caractères non valides !";
                    }
                }
            } elseif ($champ == 'timbre') {
                if (!preg_match('/^\/[A-Za-z0-9-]+(\/[A-Za-z0-9-]+)+$/', $data[$champ])) {
                    if (!isset($errors[$champ])) {
                        $errors[$champ] = "La valeur que vous avez indiquée ne respecte pas le format attendu";
                    }
                }
            }  /*elseif ($champ == 'reference') {
                if (!preg_match('/^[A-Za-z0-9]+(\/[A-Za-z0-9]+)+$/', $data[$champ])) {
                    if (!isset($errors[$champ])) {
                        $errors[$champ] = "La valeur que vous avez indiquée ne respecte pas le format attendu";
                    }
                }
            }*/
        }

        // Validation des dates
        if (!empty($data['date_debut']) && !empty($data['date_fin']) && $data['date_fin'] < $data['date_debut']) {
            $errors['date_fin'] = "La date de fin doit être égale ou postérieure à la date de début.";
        }

        // Récupération du titre et de l'indemnité forfaitaire
        $titres = $_POST['titres'] ?? [];
        $forfaires = $_POST['indemnites'] ?? [];


        // Validation des titres associés et des indemnités forfaitaires
         $validTitre = false;

        foreach ($titres as $i => $titre) {
            $titre_val = trim($titre);
            $indem_val = isset($forfaires[$i]) ? trim($forfaires[$i]) : '';
        
            if ($titre_val !== '' || $indem_val !== '') {
                $validTitre = true;
                break;
            }
        }

        if (!$validTitre) {
            $errors['titres_associes'] = "Veuillez entrer au moins un titre ou une indemnité.";
        }



        /*
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
        }*/

        /*
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
        }*/

        // Validations spécifiques par type
        if (in_array($type_activite, ['1', '2'])) {
            if (empty($data['taux_journalier'])) {
                $errors['taux_journalier'] = "Le taux journalier est requis.";
            } elseif (!preg_match('/^\d+(\.\d{1,2})?$/', $data['taux_journalier']) || $data['taux_journalier'] < 0) {
                $errors['taux_journalier'] = "Le taux journalier doit être un montant en FCFA valide (ex. : 123.45, non négatif).";
            }
        }

        /*
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
        }*/

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

        if (empty($errors)) {
            // Vérifier si une activité identique existe déjà
            $stmt = '
                SELECT id 
                FROM activites 
                WHERE type_activite = :val1 
                AND id_user = :val2 
                AND nom = :val3 
                AND description = :val4 
                AND date_debut = :val5 
                AND date_fin = :val6 
                AND centre = :val7 
                AND premier_responsable = :val8 
                AND titre_responsable = :val9 
                AND organisateur = :val10 
                AND titre_organisateur = :val11 
                AND financier = :val12 
                AND titre_financier = :val13 
                AND timbre = :val14 
                AND reference = :reference 
                AND mode_payement = :mode_payement ';
            
            if (in_array($type_activite, ['1', '2'])) {
                $stmt .= 'AND taux_journalier = :val15 ';
            } else {
                $stmt .= 'AND taux_journalier IS NULL ';
            }

            if ($type_activite == '3') {
                $stmt .= 'AND taux_taches = :val16 AND frais_deplacement_journalier = :val17';
            } else {
                $stmt .= 'AND taux_taches IS NULL AND frais_deplacement_journalier IS NULL';
            }

            $stmt .= ' AND id != :id_activite';
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
            $sql->bindParam('id_activite', $activity_id, PDO::PARAM_INT);
            $sql->bindParam('mode_payement', $data['mode_payement'], PDO::PARAM_INT);

            if (in_array($type_activite, ['1', '2'])) {
                $sql->bindParam('val15', $data['taux_journalier']);
            }
            if ($type_activite == '3') {
                $sql->bindParam('val16', $data['taux_taches']);
                $sql->bindParam('val17', $data['frais_deplacement_journalier']);
            }
            $sql->execute();

            if ($sql->rowCount() != 0) {
                $_SESSION['erreur_modifier_infos'] = 'Il semble que vous avez déjà créé une activité avec les mêmes informations.';
                header('Location: ../gerer_activite.php?id=' . chiffrer($activity_id));
                exit;
            } else {
                try {
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
                        frais_deplacement_journalier = :frais_deplacement_journalier,
                        mode_payement = :mode_payement
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
                        'titre_responsable' => $data['titre_responsable'],
                        'organisateur' => $data['organisateur'],
                        'titre_organisateur' => $data['titre_organisateur'],
                        'financier' => $data['financier'],
                        'titre_financier' => $data['titre_financier'],
                        'taux_journalier' => in_array($type_activite, ['1', '2']) ? $data['taux_journalier'] : null,
                        'taux_taches' => $type_activite === 3 ? $data['taux_taches'] : null,
                        'frais_deplacement_journalier' => $type_activite === 3 ? $data['frais_deplacement_journalier'] : null,
                        'mode_payement' => $data['mode_payement'],
                        'id' => $activity_id,
                        'id_user' => $id_user
                    ]);

                    /*
                    // Mettre à jour les diplômes
                    $diplomes_str = implode(',', $diplomes);
                    $sql_diplome = 'UPDATE diplomes SET noms = :noms WHERE id_activite = :id_activite';
                    $stmt_diplome = $bdd->prepare($sql_diplome);
                    $stmt_diplome->execute([
                        'id_activite' => $activity_id,
                        'noms' => $diplomes_str
                    ]);*/

                    // Gestion robuste des titres
                    try {
                        $bdd->beginTransaction();

                        // Récupérer les titres existants
                        $stmt = $bdd->prepare('SELECT id_titre, nom FROM titres WHERE id_activite = :id_activite ORDER BY id_titre ASC');
                        $stmt->execute(['id_activite' => $activity_id]);
                        $anciens_titres = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        // On va traiter les titres par position
                        $nb_anciens = count($anciens_titres);
                        $nb_nouveaux = count($titres);

                        // Mise à jour des titres existants (par position)
                        for ($i = 0; $i < min($nb_anciens, $nb_nouveaux); $i++) {
                            $ancien_nom = $anciens_titres[$i]['nom'];
                            $nouveau_nom = $titres[$i];
                            $id_titre = $anciens_titres[$i]['id_titre'];
                            $indemnite = (in_array($type_activite, ['2', '3'])) ? $forfaires[$i] ?? null : null;

                            // Si le nom ou l'indemnité a changé, on met à jour
                            if ($ancien_nom !== $nouveau_nom || $indemnite !== null) {
                                $stmt_update = $bdd->prepare('UPDATE titres SET nom = :nom, indemnite_forfaitaire = :indemnite WHERE id_titre = :id_titre AND id_activite = :id_activite');
                                $stmt_update->execute([
                                    'nom' => $nouveau_nom,
                                    'indemnite' => $indemnite,
                                    'id_titre' => $id_titre,
                                    'id_activite' => $activity_id
                                ]);
                            }
                        }

                        // Supprimer les titres excédentaires (si moins de titres dans le formulaire)
                        if ($nb_nouveaux < $nb_anciens) {
                            for ($i = $nb_nouveaux; $i < $nb_anciens; $i++) {
                                $id_titre = $anciens_titres[$i]['id_titre'];
                                $stmt_delete = $bdd->prepare('DELETE FROM titres WHERE id_titre = :id_titre AND id_activite = :id_activite');
                                $stmt_delete->execute([
                                    'id_titre' => $id_titre,
                                    'id_activite' => $activity_id
                                ]);
                            }
                            // Message d'alerte si suppression
                            $_SESSION['liaison_impactee'] = [
                                'message' => "La liaison de certains acteurs à votre activité a été rompue. Si vous voulez leur associer de nouveaux titres <a href='/gestion_participants/liaison.php?s=1&id=" . chiffrer($activity_id) . "' class='alert-link'>cliquez ici</a>. Si vous ne faites rien, la liaison avec ces acteurs restera rompue.",
                                'id_activite' => $activity_id
                            ];
                        }

                        // Ajouter les nouveaux titres (si plus de titres dans le formulaire)
                        if ($nb_nouveaux > $nb_anciens) {
                            for ($i = $nb_anciens; $i < $nb_nouveaux; $i++) {
                                $nom = $titres[$i];
                                $indemnite = (in_array($type_activite, ['2', '3'])) ? $forfaires[$i] ?? null : null;
                                $stmt_insert = $bdd->prepare('INSERT INTO titres (id_activite, nom, indemnite_forfaitaire) VALUES (:id_activite, :nom, :indemnite)');
                                $stmt_insert->execute([
                                    'id_activite' => $activity_id,
                                    'nom' => $nom,
                                    'indemnite' => $indemnite
                                ]);
                            }
                        }

                        $bdd->commit();
                    } catch (PDOException $e) {
                        $bdd->rollBack();
                        $errors['titres_associes'] = "Erreur lors de la synchronisation des titres : " . $e->getMessage();
                    }

                    // Afficher les titres après mise à jour pour débogage
                    $stmt = $bdd->prepare('SELECT id_titre, nom, indemnite_forfaitaire FROM titres WHERE id_activite = :id_activite');
                    $stmt->execute(['id_activite' => $activity_id]);
                    $titres_apres = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <pre><?php echo "Affichage de la table titres après la requête"; var_dump($titres_apres); ?></pre>
                    <?php

                    // Stocker le hash de la soumission
                    $_SESSION['last_submission_hash'] = [
                        'hash' => $submission_hash,
                        'time' => time()
                    ];

                    // Pour afficher message de succès
                    $success = true;
                    $_POST = []; // Vider la superglobale

                    if ($success) {
                        $_SESSION['success'] = 'Vos modifications ont été enregistrées avec succès !';
                        header('Location: ../gerer_activite.php?id=' . chiffrer($activity_id));
                        exit;
                    }
                } catch (PDOException $e) {
                    $errors['database'] = "Une erreur s'est produite : " . $e->getMessage();
                }
            }
        }
    }

    // Si erreurs, stocker les données et erreurs dans la session pour affichage
    if (!empty($errors)) {
        $_SESSION['form_data'] = $data;
        $_SESSION['form_errors'] = $errors;
        header('Location: ../modifier_infos.php?id=' . chiffrer($activity_id));
        exit;
        //var_dump($errors); 

        //echo "Bonjour 1";
    }
}
?>