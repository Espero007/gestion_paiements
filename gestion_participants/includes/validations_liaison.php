<?php

for ($i = 0; $i < count($participants); $i++) {
    foreach ($champs_attendus as $champ) {
        if (!isset($_POST[$champ][$i]) || (isset($_POST[$champ][$i]) && empty($_POST[$champ][$i]))) {
            if ($champ == 'compte_bancaire') {
                $erreurs[$champ][$i][] = 'Veuillez sélectionner un compte bancaire';
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
            } else if ($champ == 'diplome') {
                if (!in_array($valeur, $diplomes[$i])) {
                    $erreurs[$champ][$i][] = "Le diplome que vous avez choisi n'est pas valide";
                }
            } else if ($champ == 'compte_bancaire') {
                if (!in_array($valeur, $id_comptes[$i])) {
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