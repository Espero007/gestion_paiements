<?php
// On vérifie la présence de l'id de l'activité à gérer et si elle n'est pas présente on redirige vers la page précédente

if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// Arrivé ici on a une activité valide
$id_activite = dechiffrer($_GET['id']);

// Vérifions si on a déjà défini pour cette activité les informations de l'entête

$stmt = $bdd->query('SELECT id FROM informations_entete WHERE id_activite=' . $id_activite);
$entete_editee = $stmt->rowCount() == 0 ? false : true;

// Prenons la liste des banques dont on peut générer les ordres de virement
$stmt = $bdd->prepare(
    'SELECT DISTINCT banque
    FROM participations pa
    INNER JOIN participants p ON pa.id_participant = p.id_participant
    INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
    WHERE p.id_user=' . $_SESSION['user_id'] . ' AND pa.id_activite=' . $id_activite
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
    $documents[$cle] = 'Ordre de virement ' . $banque[0];
}
$documents['synthese_ordres_virements'] = 'Synthèse des ordres de virements';
$documents['liste_rib'] = 'Liste des RIBs';
$documents['documents_fusionnes'] = 'Document rassemblant tous les autres';

// Gestion des documents sélectionnés

// Mise en place des urls de téléchargement

$urls = [
    'note_service' => '/gestion_activites/scripts_generation/note_service.php?id=' . chiffrer($id_activite),
    'attestation_collective' => '/gestion_activites/scripts_generation/attestation.php?id=' . chiffrer($id_activite),
    'etat_paiement' => '/gestion_activites/scripts_generation/etat_paiement.php?id=' . chiffrer($id_activite)
];

foreach ($banques as $banque) {
    $cle = strtolower(str_replace(" ", '_', 'ordre_virement_' . supprimerAccents($banque[0])));
    $urls[$cle] = '/gestion_activites/scripts_generation/ordre_virement.php?id=' . chiffrer($id_activite) . '&banque=' . $banque[0];
}
$urls['synthese_ordres_virements'] = '/gestion_activites/scripts_generation/synthese_ordres_virements.php?id=' . chiffrer($id_activite);
$urls['liste_rib'] = '/gestion_activites/scripts_generation/liste_des_RIB.php?id=' . chiffrer($id_activite);
$urls['documents_fusionnes'] = '/gestion_activites/scripts_generation/document_fusionne.php?id=' . chiffrer($id_activite);

$pdfs = [];
$pdfs_non_telechargeables = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($documents as $document => $label) {
        if (in_array($document, $_POST)) {
            $documents_choisis[] = $document;
            if (!in_array($document, $pdfs_non_telechargeables)) {
                $pdfs[] = $urls[$document];
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['generer_zip_selection']) || isset($_POST['generer_zip_tous']))) {

    if (isset($_POST['documents']) && isset($_POST['generer_zip_selection'])) {
        $documents_a_zipper = $_POST['documents'];
        foreach ($documents_a_zipper as $document) {
            $_SESSION['documents'][] = $document;
        }
        header('location:/gestion_activites/scripts_generation/fichier_zip.php?id=' . chiffrer($id_activite) . '&param=s'); // s pour sélection
    } elseif (isset($_POST['generer_zip_tous'])) {
        header('location:/gestion_activites/scripts_generation/fichier_zip.php?id=' . chiffrer($id_activite) . '&param=t'); // t pour tous
    }
    exit;
}
