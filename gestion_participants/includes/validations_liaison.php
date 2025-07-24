<?php

if ($sens == 1) {
    $champs_attendus = ['titre', 'compte_bancaire', 'nbr_jours'];

    if ($type_activite == 3) {
        $champs_attendus[] = 'nbr_taches';
    }

    for ($i = 0; $i < count($participants); $i++) {
        foreach ($champs_attendus as $champ) {
            if (!isset($_POST[$champ][$i]) || (isset($_POST[$champ][$i]) && empty($_POST[$champ][$i]))) {
                if ($champ == 'compte_bancaire') {
                    $erreurs[$champ][$i][] = 'Veuillez sélectionner un compte bancaire';
                } elseif ($champ == 'nbr_taches' && isset($_POST[$champ][$i])) {
                    if ($_POST[$champ][$i] == 0) {
                        $erreurs[$champ][$i][] = 'Veuillez indiquer une valeur numérique valide';
                    }
                } else {
                    $erreurs[$champ][$i][] = 'Veuillez remplir ce champ';
                }
            } else {
                $valeur = $_POST[$champ][$i];

                // Les champs sont présents et relativement non vides
                if ($champ == 'titre') {
                    if (!in_array($valeur, $titres_intitules)) {
                        $erreurs[$champ][$i][] = "Le titre que vous avez choisi n'est pas valide";
                    }
                } else if ($champ == 'compte_bancaire') {
                    if (!in_array(dechiffrer($valeur), $id_comptes[$i])) {
                        $erreurs[$champ][$i][] = "Le compte bancaire sélectionné n'est pas valide";
                    }
                } else if ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
                    if (!filter_var($valeur, FILTER_VALIDATE_INT)) {
                        $erreurs[$champ][$i][] = "Vous devez indiquer une valeur numérique valide";
                    }
                }
            }
        }
    }
} elseif ($sens == 0) {
    for ($i = 0; $i < count($activites); $i++) {
        $champs_attendus = ['titre', 'compte_bancaire', 'nbr_jours'];
        if ($activites[$i]['type_activite'] == 3) {
            $champs_attendus[] = 'nbr_taches';
        }

        foreach ($champs_attendus as $champ) {
            if (!isset($_POST[$champ][$i]) || (isset($_POST[$champ][$i]) && empty($_POST[$champ][$i]))) {
                if ($champ == 'compte_bancaire') {
                    $erreurs[$champ][$i][] = 'Veuillez sélectionner un compte bancaire';
                } elseif ($champ == 'nbr_taches' && isset($_POST[$champ][$i])) {
                    if ($_POST[$champ][$i] == 0) {
                        $erreurs[$champ][$i][] = 'Veuillez indiquer une valeur numérique valide';
                    }
                } else {
                    $erreurs[$champ][$i][] = 'Veuillez remplir ce champ';
                }
            } else {
                $valeur =  $_POST[$champ][$i];

                if ($champ == 'titre') {
                    if (!in_array($valeur, $titres_intitules[$i])) {
                        $erreurs[$champ][$i][] = 'Le titre que vous avez choisi n\'est pas valide';
                    }
                } elseif ($champ == 'compte_bancaire') {
                    if (!in_array(dechiffrer($valeur), $ids_comptes)) {
                        $erreurs[$champ][$i][] = "Le compte bancaire sélectionné n'est pas valide";
                    }
                } elseif ($champ == 'nbr_jours' || $champ == 'nbr_taches') {
                    if (!filter_var($valeur, FILTER_VALIDATE_INT)) {
                        $erreurs[$champ][$i][] = "Vous devez indiquer une valeur numérique valide";
                    }
                }
            }
        }
    }
}
