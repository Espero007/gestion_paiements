<?php
// On vérifie la présence de l'id de l'activité à gérer et si elle n'est pas présente on redirige vers la page précédente
$redirect = true;

if (filter_input(INPUT_GET, 'id_activite', FILTER_VALIDATE_INT)) {
    // On s'assure d'abord que l'activité en question a des participants associés, soit elle est présente dans la table participations
    $stmt = $bdd->prepare('SELECT id FROM participations WHERE id_activite=' . $_GET['id_activite']);
    $stmt->execute();
    $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultat) != 0) {
        if (valider_id('', '', $bdd, 'activites', $_GET['id_activite'])) {
            $id_activite = $_GET['id_activite'];
            $redirect = false;
        }
    }
}

if ($redirect) {
    header('location:' . $_SESSION['previous_url']);
    exit;
}

// Arrivé ici on a une activité valide

// Prenons la liste des banques dont on peut générer les ordres de virement
$stmt = $bdd->prepare(
    'SELECT DISTINCT banque
    FROM participations pa
    INNER JOIN participants p ON pa.id_participant = p.id_participant
    INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
    WHERE p.id_user='. $_SESSION['user_id'] .' AND pa.id_activite='.$id_activite
);
$stmt->execute();
$banques = $stmt->fetchAll(PDO::FETCH_NUM);

$documents = [
    'note_service' => 'Note de service',
    'attestation_collective' => 'Attestation collective de travail',
    'etat_paiement' => 'Etat de paiement',
];
foreach ($banques as $banque) {
    $cle = strtolower(str_replace(" ", '_', 'ordre_virement_' . supprimerAccents($banque[0])));
    $documents[$cle] = 'Ordre de virement '.$banque[0];
}
$documents['synthese_ordres_virements'] = 'Synthèse des ordres de virements';
$documents['liste_rib'] = 'Liste des RIBs';

// Gestion des documents sélectionnés

// Mise en place des urls de téléchargement

$urls = [
    'note_service' => '/gestion_activites/scripts_generation/note_attestation.php?document=note&id=' . $id_activite,
    'attestation_collective' => '/gestion_activites/scripts_generation/note_attestation.php?document=attestation&id='.$id_activite,
    'etat_paiement' => 'url à définir',
];

foreach ($banques as $banque) {
    $cle = strtolower(str_replace(" ", '_', 'ordre_virement_' . supprimerAccents($banque[0])));
    $urls[$cle] = '/gestion_activites/scripts_generation/ordre_virement.php?id='.$id_activite.'&banque=' . $banque[0];
}
$urls['synthese_ordres_virements'] = '/gestion_activites/scripts_generation/synthese_ordres_virements.php?id='.$id_activite;
$urls['liste_rib'] = 'Url à définir';

$pdfs_non_telechargeables = ['etat_paiement', 'liste_rib'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($documents as $document => $label) {
        if (in_array($document, $_POST)) {
            $documents_choisis[] = $document;
            if(!in_array($document, $pdfs_non_telechargeables))
            $pdfs[] = $urls[$document];
        }
    }
}
