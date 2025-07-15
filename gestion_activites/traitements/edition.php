<?php

// Validation de l'id
if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_activite = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Je veux le faire en deux volets, d'abord pour ajouter, ensuite pour modifier. Les deux aujourd'hui. Ok travaillons le alors
$donnees = [
    'ligne1' => '',
    'ligne2' => '',
    'ligne3' => '',
    'ville' =>  '',
    'date1' => '',
    'date2' => ''
];

foreach ($donnees as $key => $value) {
    $champs_attendus[] = $key;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer'])) {

    foreach ($champs_attendus as $champ) {
        if (!isset($_POST[$champ])) {
            redirigerVersPageErreur(404, $_SESSION['previous_url']);
        }
        if (!empty($_POST[$champ])) {
            // En gros on va s'assurer que la valeur indiquée est valide avec un regex
            if (!preg_match('/^[\p{L}\p{N} \-\']+$/u', $_POST[$champ])) {
                $erreurs[$champ][] = "Ce champ contient des caractères non valides !";
            }
        }
    }

    // foreach ($donnees as $key => $_) {
    //     $donnees[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    // }
}
