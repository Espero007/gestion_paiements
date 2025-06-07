<?php

// Validations

// Je suis supposé recevoir l'id du participant sélectionné donc on va partir dessus et la valider
if (!valider_id('get', 'id_participant', $bdd, 'participants')) {
    redirigerVersPageErreur(404, obtenirURLcourant());
}

// A ce niveau l'id est valide donc on passe à la suite

// Récupérons les activités déjà créées par cet utilisateur

$stmt = "
SELECT id, type_activite, id_user, nom, description, date_debut, date_fin, centre
FROM activites
WHERE id_user = " . $_SESSION['user_id'];
$stmt = $bdd->prepare($stmt);
$stmt->execute();
$activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$activites) {
    redirigerVersPageErreur(500, obtenirURLcourant());
}
