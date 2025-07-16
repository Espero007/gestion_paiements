<?php

// Validation de l'id
if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
    // echo 'ici';
}
$id_activite = $_GET['id'];
$user_id = $_SESSION['user_id'];
$modification = false;

$stmt = $bdd->query('SELECT * FROM informations_entete WHERE id_activite=' . $id_activite);
if ($stmt->rowCount() != 0) {
    $informations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $informations = $informations[0];
    $modification = true;
}

// Je veux le faire en deux volets, d'abord pour ajouter, ensuite pour modifier. Les deux aujourd'hui. Ok travaillons le alors
$champs_attendus = [
    'ligne1' => 'Ligne 1',
    'ligne2' => 'Ligne 2',
    'ligne3' => 'Ligne 3',
    'ville' =>  'Ville',
    'date1' => 'Date 1',
    'date2' => 'Date 2'
];

foreach ($champs_attendus as $key => $value) {
    $donnees[$key] = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer'])) {

    foreach ($champs_attendus as $champ) {
        // if (!isset($_POST[$champ])) { 
        //     redirigerVersPageErreur(404, $_SESSION['previous_url']);
        // }
        if (!empty($_POST[$champ])) {
            // En gros on va s'assurer que la valeur indiquée est valide
            if (!preg_match('/^[\p{L}\p{N} \-\']+$/u', $_POST[$champ])) {
                $erreurs[$champ][] = "Ce champ contient des caractères non valides !";
            }
        }
    }

    foreach ($donnees as $key => $_) {
        $donnees[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    if (!isset($erreurs)) {
        // On démarre les insertions
        if(!$modification){
            $stmt = $bdd->prepare('INSERT INTO informations_entete(id_activite, ligne1, ligne2, ligne3, ville, date1, date2) VALUES (' . $id_activite . ', :ligne1, :ligne2, :ligne3, :ville, :date1, :date2)');
        }else{
            // Une modification
            $stmt = $bdd->prepare('UPDATE informations_entete SET ligne1=:ligne1, ligne2=:ligne2, ligne3=:ligne3, ville=:ville, date1=:date1, date2=:date2 WHERE id_activite='.$id_activite);
        }

        $stmt->execute([
            'ligne1' => !empty($_POST['ligne1']) ? $_POST['ligne1'] : null,
            'ligne2' => !empty($_POST['ligne2']) ? $_POST['ligne2'] : null,
            'ligne3' => !empty($_POST['ligne3']) ? $_POST['ligne3'] : null,
            'ville' => !empty($_POST['ville']) ? $_POST['ville'] : null,
            'date1' => !empty($_POST['date1']) ? $_POST['date1'] : null,
            'date2' => !empty($_POST['date2']) ? $_POST['date2'] : null
        ]);

        $compteur = 0;

        foreach ($donnees as $donnee) {
            if (empty($donnee)) {
                $compteur++;
            }
        }

        if ($compteur == count($donnees)) {
            // aucune donnée n'a été modifiée
            $_SESSION['edition_entete_ok'] = 'Il semble que vous n\'avez saisi aucune valeur. Les informations par défaut seront conservées.';
        } else {
            $_SESSION['edition_entete_ok'] = 'Les informations que vous avez saisi ont été enregistrées avec succès !';
        }
        header('location:' . $_SESSION['previous_url']);
        exit;
    }
}
