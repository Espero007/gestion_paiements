<?php
// Fichier : create_activity.php
session_start();
require_once(__DIR__ . '/../auth/submit/variables.php');
// require_once(__DIR__ . '/header.php');
//require_once(__DIR__ . '/function.php');

// $loggedUser = $_SESSION['loguser'];

$errors = [];
$success = false;
$type = 2;
$id_user  = $_SESSION['user_id'];
$diplomes = [];
$titres = [];
$forfaires = [];

// Initialisation des données à vide
$data = [
    'nom' => '',
    'description' => '',
    'periode' => '',
    'centre' => '',
    'responsable' => '',
    'organisateur' => '',
    'financier' => '',
    'note_generatrice' => '',
    'niveaux_diplome' => '', 
    'titres_associes' => '',
    'taux_journalier' => '',
    'indemnites_forfaitaires' => '',   
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $key => $_) {
        $data[$key] = isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    // Validation simple
    if (empty($data['nom'])) $errors['nom'] = "Le nom est requis.";
    if (empty($data['description'])) $errors['description'] = "La description est requise.";
    if (empty($data['periode'])) $errors['periode'] = "La periode est requise.";
    if (empty($data['centre'])) $errors['centre'] = "Le centre est requis.";
    if (empty($data['responsable'])) $errors['responsable'] = "Le nom du responsable titre est requis.";
    if (empty($data['organisateur'])) $errors['organisateur'] = "Le nom de l'organisateur est requis.";
    if (empty($data['financier'])) $errors['financier'] = "Le nom du financier titre  est requis.";
    if (empty($data['niveaux_diplome'])) $errors['niveaux_diplome'] = "Les niveaux de diplomes sont requis.";
    if (empty($data['titres_associes'])) $errors['titres_associes'] = "Le titres associés à l'activité sont requis.";
    if (empty($data['taux_journalier'])) $errors['taux_journalier'] = "Le taux journalier est requis.";
    if (empty($data['indemnites_forfaitaires'])) $errors['indemnites_forfaitaires'] = "L'endemnité forfaitaire est requis.";

    // Gestion du fichier note_generatrice
    if (isset($_FILES['note_generatrice']) && $_FILES['note_generatrice']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['note_generatrice']['tmp_name'];
        $fileName = basename($_FILES['note_generatrice']['name']);
        $uploadFileDir = __DIR__ . '/uploads/'; // Chemin absolu

        // Créer le dossier Uploads
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }

        $dest_path = $uploadFileDir . $fileName;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $data['note_generatrice'] = $fileName;
        } else {
            $errors['note_generatrice'] = "Échec du déplacement du fichier. Vérifiez les permissions.";
        }
    }

    if ($data['taux_journalier'] !== '') {
        if (filter_var($data['taux_journalier'], FILTER_VALIDATE_FLOAT) === false) {
            $errors['taux_journalier'] = "Le taux journalier doit être un nombre décimal valide (ex. : 123.45).";
        }
    }


    // Valisation des titres 
    if ($data['titres_associes'] != '' &&  strpos($data['titres_associes'], ',,') !== false){
        $errors['titres_associes'] = "Les titres contiennent des virgules consécutives non valides.";
    }
    else if ($data['titres_associes'] != '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['titres_associes'])) {
        $errors['titres_associes'] = "Les titres doivent être séparés par des virgules (ex. : Conférence,Atelier).";
    }
    else {
        $titres = array_map('trim', explode(',', $data['titres_associes']));
    }

    foreach ($titres as $titre) {
        if (empty($titre)) {
            $errors['titres_associes'] = "Chaque titre doit être une chaîne non vide.";
            break;
        }
    }


    // Validation des forfaires
    if ($data['indemnites_forfaitaires'] != '' &&  strpos($data['indemnites_forfaitaires'], ',,') !== false){
        $errors['indemnites_forfaitaires'] = "Les titres contiennent des virgules consécutives non valides.";
    }
    else if ($data['indemnites_forfaitaires'] != '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['indemnites_forfaitaires'])) {
        $errors['indemnites_forfaitaires'] = "Les titres doivent être séparés par des virgules (ex. : Conférence,Atelier).";
    }
    else { 
        $forfaires = array_map('trim', explode(',', $data['indemnites_forfaitaires']));
    }

    foreach ($forfaires as $forfaire) {
        if (filter_var($forfaire, FILTER_VALIDATE_FLOAT) === false) {
            $errors['indemnites_forfaitaires'] = "Chaque indemnité doit être un nombre décimal valide (ex. : 123.45).";
            break;
        }
    }

    // Vérifier que le nombre de titres correspond au nombre d'indemnités
    if (count($titres) !== count($forfaires)) {
        $errors['titres_associes'] = "Le nombre d'indemnités forfaitaires doit être égal au nombre de titres.";
        $errors['indemnites_forfaitaires'] = "Le nombre d'indemnités forfaitaires doit être égal au nombre de titres.";
    }



    // Validation des niveaux de diplômes 
    if ($data['niveaux_diplome'] != '' &&  strpos($data['niveaux_diplome'], ',,') !== false){
        $errors['niveaux_diplome'] = "Les titres contiennent des virgules consécutives non valides.";
    }
    else if ($data['niveaux_diplome'] != '' && !preg_match('/^[^,]+(,[^,]+)*$/', $data['niveaux_diplome'])) {
        $errors['niveaux_diplome'] = "Les titres doivent être séparés par des virgules (ex. : Conférence,Atelier).";
    }
    else {
        $diplomes = array_map('trim', explode(',', $data['niveaux_diplome']));
    }

    foreach ($diplomes as $diplome) {
        if (empty($diplome)) {
            $errors['niveaux_diplome'] = "Chaque titre doit être une chaîne non vide.";
            break;
        }
    }


        /*
    // Nettoyage des champs numériques
    $numerical_fields = ['taux_journalier',];
    foreach ($numerical_fields as $field) {
        if ($data[$field] === '') {
            $data[$field] = null;
        }
    } */

   
    if (empty($errors)) {
        $sql = 'INSERT INTO activites (type_activite,id_user,nom, description, periode , centre , premier_responsable_titre, organisateur_titre, financier_titre,note_generatrice,taux_journalier , taux_taches ,frais_deplacement_journaliers)
                VALUES (:type_activite , :id_user ,:nom, :description, :periode, :centre, :responsable, :organisateur, :financier, :note_generatrice,:taux_journalier, :taux_taches, :frais_deplacement)';
        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'type_activite' => $type,
            'id_user' => $id_user ,
            'nom' => $data['nom'],
            'description' => $data['description'],
            'periode' => $data['periode'],
            'centre' => $data['centre'],
            'responsable' => $data['responsable'],
            'organisateur' => $data['organisateur'],
            'financier' => $data['financier'],
            'note_generatrice' => $data['note_generatrice'],
            'taux_journalier' => $data['taux_journalier'],
            'taux_taches' => null ,
            'frais_deplacement' => null
        ]);

        $last_id = $bdd->lastInsertId();

        $sql_ = 'INSERT INTO titres(id_activite,nom,indemnites_forfaitaires) VALUES (:id_activite , :nom , :indemnites_forfaitaires)';
        $stmt_ = $bdd->prepare($sql_);

        
        // Parcourir les tableaux avec array_combine et foreach
        foreach (array_combine($titres, $forfaires) as $val1 => $val2) {
            $stmt_->execute([
                'id_activite' =>  $last_id,
                'nom' => $val1,
                'indemnites_forfaitaires' => $val2 
            ]);      
        }

        $_sql_ = 'INSERT INTO diplomes(id_activite,nom) VALUES (:id_activite , :nom )';
        $_stmt_ = $bdd->prepare($_sql_);

        // Parcourir les tableaux avec array_combine et foreach
        foreach ($diplomes as $diplome) {
            $_stmt_->execute([
                'id_activite' => $last_id ,
                'nom' => $diplome 
            ]);
        }

        $success = true;
        
        $stmt = $bdd->prepare("SELECT id FROM activites ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $id_derniere_activite = $stmt->fetch(PDO::FETCH_NUM);

        // var_dump($ligne);

        // $id_last_activites = ;

        $url = 'http://localhost:3000/enregistrer_participant.php?id_activite=' . $id_derniere_activite[0];
        header("location: $url");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Créer une Activité</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">

</head>
<body class="container py-4">
<?php  //echo "<h2> Bienvenue sur notre site <i style = 'color:blue'>". $loggedUser["Nom"] . " ". $loggedUser["Prenom"] ."</i> </h2>"; ?>

    <h2>Création d'une Activité de type 2</h2>
    <?php if ($success): ?>
        <div class="alert alert-success">
            <strong>Activité créée avec succès !</strong>
            <ul>
                <?php foreach ($data as $key => $value): ?>
                    <li><strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $key))) ?> :</strong> <?= htmlspecialchars($value) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <div class=".container">
    <form method="POST" enctype="multipart/form-data">
        <?php foreach ($data as $key => $value): if ($key === 'note_generatrice') continue; ?>
            <div class="mb-3">
                <label class="form-label">
                    <?= ucwords(str_replace('_', ' ', $key)) ?>
                    <?php if ($key === 'niveaux_diplome' || $key === 'titres_associes' || $key === 'forfaits_par_titre'): ?>
                        <small class="text-muted">(séparés par des virgules)</small>
                    <?php endif; ?>
                </label>
                <input type="text" name="<?= $key ?>" class="form-control" value="<?= htmlspecialchars($value) ?>">
                <small class="text-danger"><?= $errors[$key] ?? '' ?></small>
            </div>
        <?php endforeach; ?>

        <div class="mb-3">
            <label class="form-label">Note génératrice (fichier)</label>
            <input type="file" name="note_generatrice" class="form-control">
            <small class="text-danger"><?= $errors['note_generatrice'] ?? '' ?></small>
        </div>

        <button type="submit" class="btn btn-primary">Créer l'activité</button>
    </form>
    </div>
</body>
</html>
