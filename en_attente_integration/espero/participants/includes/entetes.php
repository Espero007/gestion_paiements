<?php

// On inclut les entêtes liées aux informations générales

if (in_array('infos_generales', $elements_a_inclure)) {
    // Dans ces tableaux, j'indique le champ attendu, la valeur à mettre pour son label et la valeur à mettre pour son placeholder dans cet ordre. Cela pour automatiser de création des groupes et pour alléger le fichier principal qui va affciher le formulaire

    $informations_generales = [
        "nom" => ["Nom", "Entrez le nom"],
        "prenoms" => ["Prénom(s)", "Entrez le(s) prénom(s)"],
        "matricule_ifu" => ["Matricule/IFU", "Entrez le matricule ou l'IFU"],
        "date_naissance" => ["Date de naissance", ""],
        "lieu_naissance" => ["Lieu de naissance", "Entrez le lieu de naissance"]
    ];
}

if (in_array('infos_generales', $elements_a_inclure) && in_array('infos_bancaires', $elements_a_inclure)) {
    // On est sur la page d'ajout des participants
    $page_ajout_participant = true;
} elseif (!in_array('infos_generales', $elements_a_inclure) && in_array('infos_bancaires', $elements_a_inclure)) {
    // On est sur la page de modification
    $page_modification = true;
}

// On inclut les entêtes liées aux comptes bancaires

if (in_array('infos_bancaires', $elements_a_inclure)) {
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

    if (isset($page_ajout_participant) && $page_ajout_participant) {
        $nombre_comptes_existants = 0;
        $nombre_comptes_bancaires = 1;
    }

    if (isset($page_modification) && $page_modification) {
        // On est sur la page de modification du participant donc on checke le nombre de comptes du participant dont on veut modifier les informations

        $stmt = "SELECT banque, numero_compte FROM informations_bancaires WHERE id_participant=" . $id_participant;
        $resultat = $bdd->query($stmt);

        if (!$resultat) {
            redirigerVersPageErreur(500, $current_url);
        }
        $lignes = $resultat->fetchAll(PDO::FETCH_NUM);
        $nombre_comptes_existants = 0;
        $nombre_comptes_bancaires = count($lignes);
        $resultat->closeCursor();

        // Merge avec $infos_participants
        $index = 0;
        foreach ($lignes as $ligne) {
            $index++;
            $infos_participant['banque_' . $index] = $ligne[0];
            $infos_participant['numero_compte_' . $index] = $ligne[1];
        }
    }

    $index_numero_compte = $nombre_comptes_existants;

    for ($i = 1; $i <= $nombre_comptes_bancaires; $i++) {

        $index_numero_compte++;

        $informations_bancaires["banque_$index_numero_compte"][] = ($i == 1) ? "Banque" : "Banque ($i)";
        $informations_bancaires["banque_$index_numero_compte"][] = "Indiquez la banque";

        $informations_bancaires["numero_compte_$index_numero_compte"][] = ($i == 1) ? "Numéro de compte" : "Numéro de compte ($i)";
        $informations_bancaires["numero_compte_$index_numero_compte"][] = "Entrez le numéro de compte";

        $informations_bancaires["pdf_rib_$index_numero_compte"][] = ($i == 1) ? "Copie PDF du RIB" : "Copie PDF du RIB ($i)";
        $informations_bancaires["pdf_rib_$index_numero_compte"][] = "";

        $fichiers_attendus[] = "pdf_rib_$index_numero_compte";
    }
}
