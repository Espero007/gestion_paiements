<?php

if (isset($page_modification) && $page_modification) {
    $matricule_ifu = $infos_participant['matricule_ifu'];
}

foreach ($informations_generales as $champ => $intitule_champ) {

    // 1- Vérifier tout d'abord la présence de tous les champs attendus

    if (!array_key_exists($champ, $_POST)) {
        // Si un champ est manquant, on dirige vers la page 404 tout simplement
        redirigerVersPageErreur(404, $current_url);
    } else {
        // Le champ ne manque pas à l'appel
        // 2- S'assurer à présent qu'il n'est pas vide

        $valeur_champ = $_POST[$champ];

        if (empty($valeur_champ)) {
            $erreurs[$champ][] = "Veuillez remplir ce champ";
        } else {
            // Le champ en cours n'est pas vide
            if ($champ == "nom" || $champ == "prenoms" || $champ == "lieu_naissance") {
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
                    $message = "La valeur indiquée existe déjà. Le matricule/IFU est supposé unique par participant !";
                    // La valeur semble valide mais vérifions si elle se retrouve ou non dans la base de données

                    $stmt = $bdd->prepare("SELECT matricule_ifu FROM participants WHERE matricule_ifu = :val");
                    $stmt->bindParam('val', $valeur_champ, PDO::PARAM_INT);
                    $stmt->execute();
                    $ligne = $stmt->fetch(PDO::FETCH_NUM);

                    if ($ligne) {
                        // Une ligne a été retrouvée
                        $matricule_retrouve = $ligne[0];
                        if (!isset($page_modification)) {
                            // On est pas sur la page de modification
                            $erreurs[$champ][] = $message;
                        } elseif ($matricule_retrouve != $matricule_ifu) {
                            $erreurs[$champ][] = $message;
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
                            $erreurs[$champ][] = "Le participant que vous souhaitez enregistrer semble avoir moins de 18 ans !";
                        }
                    }
                }
            }
        }
    }
}
