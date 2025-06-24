<?php
session_start();
/*
const MYSQL_HOST = 'localhost' ;
const MYSQL_PORT = 3306;
const MYSQL_NAME = 'gestion_paiements';
const MYSQL_USER = 'root';
const MYSQL_PASSWORD = '';

try {
    $bdd = new PDO (sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8',MYSQL_HOST,MYSQL_NAME,MYSQL_PORT),MYSQL_USER,MYSQL_PASSWORD) ;
    
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $exception) {
    die('Erreur : ' . $exception->getMessage());
}    */

require_once(__DIR__.'/../../includes/bdd.php');

/*
$loggedUser = $_SESSION['loguser'] ?? null;
if (!$loggedUser) {
    header('Location: connexion.php');
    exit;
} */

/*
// Vérifier si l'ID de l'activité est fourni
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location: .php');
    exit;
}
$activity_id = $_GET['id'];
*/

$activity_id =1;

// $activity_id =1099;

// Récupérer les données de l'activité
try {
    $sql = 'SELECT a.*, f.nom_original AS note_generatrice_name 
            FROM activites a 
            LEFT JOIN fichiers f ON a.id_note_generatrice = f.id_fichier 
            WHERE a.id = :id AND a.id_user = :id_user';
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['id' => $activity_id, 'id_user' => $_SESSION['user_id']]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>

   <pre><?php var_dump($activity); ?></pre> 
    <?php

    /*
    if (!$activity) {
        header('Location: changeActivity.php'); // Je ne sais pas trop où envoyer l'utilisateur dans ce cas
        exit;
    }*/

    // Récupérer les diplômes
    $sql = 'SELECT noms FROM diplomes WHERE id_activite = :id';
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['id' => $activity_id]);
    $diplomes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $niveaux_diplome = $diplomes[0];

    // Récupérer les titres et indemnités
    $sql = 'SELECT nom, indemnite_forfaitaire FROM titres WHERE id_activite = :id';
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['id' => $activity_id]);
    $titres_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $titres_associes = implode(',', array_column($titres_data, 'nom'));
    $indemnite_forfaitaire = implode(',', array_filter(array_column($titres_data, 'indemnite_forfaitaire')));

} catch (PDOException $e) {
    $_SESSION['form_errors'] = ['database' => "Erreur lors de la récupération des données. Veuillez réessayer."];
    die("Erreur : " . $e->getMessage());
   // header('Location: changeActivity.php');
    //exit;
}

$type_activite = $activity['type_activite'];

// Initialisation des données pour le formulaire
$data = [
    'nom' => $activity['nom'],
    'description' => $activity['description'],
    'centre' => $activity['centre'],
    'premier_responsable' => $activity['premier_responsable'],
    'titre_responsable' => $activity['titre_responsable'] ?? '',
    'organisateur' => $activity['organisateur'],
    'titre_organisateur' => $activity['titre_organisateur'] ?? '',
    'financier' => $activity['financier'],
    'titre_financier' => $activity['titre_financier'] ?? '',
    'note_generatrice' => $activity['note_generatrice_name'] ?? '',
    'niveaux_diplome' => $niveaux_diplome,
    'titres_associes' => $titres_associes,
    'taux_journalier' => $activity['taux_journalier'] ?? '',
    'indemnite_forfaitaire' => $indemnite_forfaitaire,
    'taux_taches' => $activity['taux_taches'] ?? '',
    'frais_deplacement_journalier' => $activity['frais_deplacement_journalier'] ?? '',
    'date_debut' => $activity['date_debut'],
    'date_fin' => $activity['date_fin'],
];

// Champs à afficher dans le message de succès par type
$fields_to_display = [
    '1' => ['nom', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'note_generatrice', 'niveaux_diplome', 'titres_associes', 'taux_journalier', 'date_debut', 'date_fin'],
    '2' => ['nom', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'note_generatrice', 'niveaux_diplome', 'titres_associes', 'taux_journalier', 'indemnite_forfaitaire', 'date_debut', 'date_fin'],
    '3' => ['nom', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'note_generatrice', 'niveaux_diplome', 'titres_associes', 'indemnite_forfaitaire', 'taux_taches', 'frais_deplacement_journalier', 'date_debut', 'date_fin']
];

// Récupérer les données et erreurs de la session si présentes
$errors = $_SESSION['form_errors'] ?? [];
$data = $_SESSION['form_data'] ?? $data;
$success = isset($_GET['success']) && $_GET['success'] === '1' && isset($_SESSION['success_data']);
if ($success) {
    $data = $_SESSION['success_data'];
}

// Nettoyer les données de la session après utilisation
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
unset($_SESSION['success_data']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier l'Activité - Type <?= htmlspecialchars($type_activite) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="container py-4">
    <h2>Modification de l'Activité - Type <?= htmlspecialchars($type_activite) ?></h2>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <strong>Activité modifiée avec succès !</strong>
            <ul>
                <?php foreach ($fields_to_display[$type_activite] as $key): ?>
                    <?php if (!empty($data[$key])): ?>
                        <li><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?> :</strong> <?= htmlspecialchars($data[$key]) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (isset($errors['database'])): ?>
        <div class="alert alert-danger">
            <strong>Erreur :</strong> <?= htmlspecialchars($errors['database']) ?>
        </div>
    <?php endif; ?>
    <?php if (isset($errors['duplicate'])): ?>
        <div class="alert alert-danger">
            <strong>Erreur :</strong> <?= htmlspecialchars($errors['duplicate']) ?>
        </div>
    <?php endif; ?>
    <div class="container">
        <form method="POST" enctype="multipart/form-data" id="activityForm" action="submitchangeActivity.php">
            <input type="hidden" name="form_submitted" value="1">
            <input type="hidden" name="activity_id" value="<?= htmlspecialchars($activity_id) ?>">
            <!-- Champs communs -->
            <div class="mb-3">
                <label class="form-label">Nom</label>
                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($data['nom']) ?>">
                <small class="text-danger"><?= $errors['nom'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($data['description']) ?>">
                <small class="text-danger"><?= $errors['description'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Centre</label>
                <input type="text" name="centre" class="form-control" value="<?= htmlspecialchars($data['centre']) ?>">
                <small class="text-danger"><?= $errors['centre'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Premier responsable</label>
                <input type="text" name="premier_responsable" class="form-control" value="<?= htmlspecialchars($data['premier_responsable']) ?>">
                <small class="text-danger"><?= $errors['premier_responsable'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Titre du responsable <small class="text-muted">(facultatif)</small></label>
                <input type="text" name="titre_responsable" class="form-control" value="<?= htmlspecialchars($data['titre_responsable']) ?>">
                <small class="text-danger"><?= $errors['titre_responsable'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Organisateur</label>
                <input type="text" name="organisateur" class="form-control" value="<?= htmlspecialchars($data['organisateur']) ?>">
                <small class="text-danger"><?= $errors['organisateur'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Titre de l’organisateur <small class="text-muted">(facultatif)</small></label>
                <input type="text" name="titre_organisateur" class="form-control" value="<?= htmlspecialchars($data['titre_organisateur']) ?>">
                <small class="text-danger"><?= $errors['titre_organisateur'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Financier</label>
                <input type="text" name="financier" class="form-control" value="<?= htmlspecialchars($data['financier']) ?>">
                <small class="text-danger"><?= $errors['financier'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Titre du financier <small class="text-muted">(facultatif)</small></label>
                <input type="text" name="titre_financier" class="form-control" value="<?= htmlspecialchars($data['titre_financier']) ?>">
                <small class="text-danger"><?= $errors['titre_financier'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Niveaux de diplôme <small class="text-muted">(séparés par des virgules, lettres accentuées autorisées, sans chiffres, ex. : Licence,Master,Ingénieur)</small></label>
                <input type="text" name="niveaux_diplome" class="form-control" value="<?= htmlspecialchars($data['niveaux_diplome']) ?>">
                <small class="text-danger"><?= $errors['niveaux_diplome'] ?? '' ?></small>
            </div>
            <div class="mb-3">
                <label class="form-label">Titres associés <small class="text-muted">(séparés par des virgules, lettres uniquement, ex. : Conference,Atelier)</small></label>
                <input type="text" name="titres_associes" class="form-control" value="<?= htmlspecialchars($data['titres_associes']) ?>">
                <small class="text-danger"><?= $errors['titres_associes'] ?? '' ?></small>
            </div>

            <!-- Champ spécifique aux types 1 et 2 -->
            <?php if (in_array($type_activite, ['1', '2'])): ?>
                <div class="mb-3">
                    <label class="form-label">Taux journalier (FCFA)</label>
                    <input type="text" name="taux_journalier" class="form-control" value="<?= htmlspecialchars($data['taux_journalier']) ?>">
                    <small class="text-danger"><?= $errors['taux_journalier'] ?? '' ?></small>
                </div>
            <?php endif; ?>

            <!-- Champ spécifique aux types 2 et 3 -->
            <?php if (in_array($type_activite, ['2', '3'])): ?>
                <div class="mb-3">
                    <label class="form-label">Indemnité forfaitaire (FCFA) <small class="text-muted">(séparés par des virgules, même nombre que les titres, ex. : 100.50,200.75)</small></label>
                    <input type="text" name="indemnite_forfaitaire" class="form-control" value="<?= htmlspecialchars($data['indemnite_forfaitaire']) ?>">
                    <small class="text-danger"><?= $errors['indemnite_forfaitaire'] ?? '' ?></small>
                </div>
            <?php endif; ?>

            <!-- Champs spécifiques au type 3 -->
            <?php if ($type_activite === '3'): ?>
                <div class="mb-3">
                    <label class="form-label">Taux par tâche (FCFA)</label>
                    <input type="text" name="taux_taches" class="form-control" value="<?= htmlspecialchars($data['taux_taches']) ?>">
                    <small class="text-danger"><?= $errors['taux_taches'] ?? '' ?></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Frais de déplacement journaliers (FCFA)</label>
                    <input type="text" name="frais_deplacement_journalier" class="form-control" value="<?= htmlspecialchars($data['frais_deplacement_journalier']) ?>">
                    <small class="text-danger"><?= $errors['frais_deplacement_journalier'] ?? '' ?></small>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Note génératrice actuelle</label>
                <p><?= htmlspecialchars($data['note_generatrice']) ?: 'Aucun fichier' ?></p>
                <label class="form-label">Nouvelle note génératrice <small class="text-muted">(facultatif, remplace l'actuelle)</small></label>
                <input type="file" name="note_generatrice" class="form-control">
                <small class="text-danger"><?= $errors['note_generatrice'] ?? '' ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Date de début</label>
                <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($data['date_debut']) ?>" min="<?= date('Y-m-d') ?>">
                <small class="text-danger"><?= $errors['date_debut'] ?? '' ?></small>
            </div>

            <div class="mb-3">
                <label class="form-label">Date de fin</label>
                <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($data['date_fin']) ?>" min="<?= date('Y-m-d') ?>">
                <small class="text-danger"><?= $errors['date_fin'] ?? '' ?></small>
            </div>

            <button type="submit" class="btn btn-primary" id="submitButton">Modifier l'activité</button>
        </form>
    </div>

    <script>
        // Désactiver le bouton après le premier clic
        document.getElementById('activityForm').addEventListener('submit', function() {
            document.getElementById('submitButton').disabled = true;
        });

        // Validation des dates avant soumission
        document.getElementById('activityForm').addEventListener('submit', function(e) {
            const dateDebut = document.querySelector('input[name="date_debut"]').value;
            const dateFin = document.querySelector('input[name="date_fin"]').value;
            if (dateDebut && dateFin && dateFin < dateDebut) {
                alert("La date de fin doit être égale ou postérieure à la date de début.");
                e.preventDefault();
            }
        });

        // Mettre à jour la date minimale de date_fin en fonction de date_debut
        document.querySelector('input[name="date_debut"]').addEventListener('change', function() {
            document.querySelector('input[name="date_fin"]').min = this.value;
        });
    </script>
</body>
</html>