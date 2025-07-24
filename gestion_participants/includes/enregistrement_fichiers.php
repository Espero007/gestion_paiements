<?php

if (isset($page_modification)) {
    $stmt = "SELECT id_rib FROM informations_bancaires WHERE id_participant=" . $id_participant;
    $resultat = $bdd->query($stmt);
    if (!$resultat) {
        redirigerVersPageErreur(500, obtenirURLcourant());
    }
    $id_ribs = $resultat->fetchAll(PDO::FETCH_NUM);

    foreach ($id_ribs as $tab => $tab_value) {
        $tab_temp[] = $tab_value[0];
    }

    // J'ai récupéré les ids des fichiers ribs
    $id_ribs = $tab_temp;
}

$upload_path = creer_dossiers_upload();

foreach ($fichiers_attendus as $fichier) {

    if (array_key_exists($fichier, $_FILES) && $_FILES[$fichier]['error'] != 4) {
        // Les fichiers ne sont vides
        $infos_fichier = $_FILES[$fichier];

        // Je modifie le nom du fichier pour le préparer à son enregistrement

        $nom_fichier = $fichier; // Ici je récupère "pdf_rib_$i";
        $chiffre_fin = substr($nom_fichier, -1); // Je prends le chiffre de fin
        $nom_fichier = substr($nom_fichier, 0, -1); // Ici je garde "pdf_rib_";

        $nom_fichier = $nom_fichier . $matricule_ifu . "_" . $chiffre_fin . ".pdf"; // Je constitue le nom final

        if (!isset($page_modification)) {
            // J'enregistre le fichier
            $chemin_absolu = $upload_path . $nom_fichier;
            // echo $chemin_absolu;
        } else {
            $stmt = $bdd->query('SELECT chemin_acces FROM fichiers WHERE id_fichier=' . $id_ribs[$chiffre_fin - 1]);
            $chemin_acces = $stmt->fetch(PDO::FETCH_NUM);
            // On est dans une modification
            $chemin_absolu = $chemin_acces[0];
        }

        if (move_uploaded_file($infos_fichier['tmp_name'], $chemin_absolu)) {
            // Enregistrement des métadonnées

            if (!isset($page_modification)) {
                $stmt = $bdd->prepare("INSERT INTO fichiers(chemin_acces, nom_original, date_upload) VALUES (:val1, :val2, :val3)");
                $stmt->bindParam(':val1', $chemin_absolu);
            } else {
                // On modifie les informations

                // Il faut que j'identifie la ligne que je veux modifier. Pour ce faire je vais passer par la table informations_bancaires et récupérer l'id_rib associé au numéro de compte. Le travail a été fait déjà dans id_ribs donc je vais poursuivre en accédant aux valeurs tout simplement

                $stmt = $bdd->prepare("UPDATE fichiers SET nom_original=:val2, date_upload=:val3 WHERE id_fichier=" . $id_ribs[$chiffre_fin - 1]);
                // echo "Je suis ici et voici l'id fichier : ". $id_ribs[$chiffre_fin - 1];
            }


            $type_fichier = 'copie_rib';
            $stmt->bindParam(':val2', $infos_fichier['name']); // nom original
            $date_upload = date("Y-m-d H:i:s"); //  peut être : 2001-03-10
            $stmt->bindParam(':val3', $date_upload);
            // $extension = strtolower(pathinfo($infos_fichier['name'], PATHINFO_EXTENSION)); // Je n'en ai plus besoin
            // $stmt->bindParam(':val4', $type_fichier); // type du fichier

            if (!$stmt->execute()) {
                redirigerVersPageErreur(500, obtenirURLcourant());
            }

            // A ce niveau, les métadonnées ont été enregistrées avec succès. On récupère ensuite l'id du fichier qui vient d'être enregistré

            $id_fichier = $bdd->lastInsertId();

            // On s'attaque ensuite à la table des informations bancaires

            if (!isset($page_modification)) {
                $stmt = $bdd->prepare("INSERT INTO informations_bancaires(id_participant, banque, numero_compte, id_rib) VALUES (:val1, :val2, :val3, :val4)");
                $stmt->bindParam(':val1', $id_participant);
                $stmt->bindParam(':val4', $id_fichier, PDO::PARAM_INT);
            } else {
                $stmt = $bdd->prepare("UPDATE informations_bancaires SET banque=:val2, numero_compte=:val3 WHERE id_rib=" . $id_ribs[$chiffre_fin - 1]);
            }

            $stmt->bindParam(':val2', $_POST['banque_' . $chiffre_fin]);
            $stmt->bindParam(':val3', $_POST['numero_compte_' . $chiffre_fin]);

            if (!$stmt->execute()) {
                redirigerVersPageErreur(500, obtenirURLcourant());
            }
            // Si on arrive ici c'est que nous sommes à la fin du processus
            $traitement_fichiers_ok = true;
        }
    } elseif (isset($page_modification)) {
        // On a modifié aucun champ de type fichier
        $traitement_fichiers_ok = true;
    }
}
