<?php
$section = 'Activités';
$titre = "Modifications des informations";
require_once(__DIR__ . '/../includes/header.php');

// Vérifier si l'ID de l'activité est fourni
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location:' . $_SESSION["previous_url"]);
    exit;
}

$activity_id = $_GET['id'];


// Récupérer les données de l'activité
try {


    $sql = 'SELECT a.* 
            FROM activites a 
            WHERE a.id = :id AND a.id_user = :id_user';
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['id' => $activity_id, 'id_user' => $_SESSION['user_id']]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);

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
    'timbre' => $activity['timbre'],
    'reference' => $activity['reference'],
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
    '1' => ['nom', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'note_generatrice', 'niveaux_diplome', 'titres_associes', 'taux_journalier', 'date_debut', 'date_fin', 'timbre', 'reference'],
    '2' => ['nom', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'note_generatrice', 'niveaux_diplome', 'titres_associes', 'taux_journalier', 'indemnite_forfaitaire', 'date_debut', 'date_fin', 'timbre', 'reference'],
    '3' => ['nom', 'description', 'centre', 'premier_responsable', 'titre_responsable', 'organisateur', 'titre_organisateur', 'financier', 'titre_financier', 'note_generatrice', 'niveaux_diplome', 'titres_associes', 'indemnite_forfaitaire', 'taux_taches', 'frais_deplacement_journalier', 'date_debut', 'date_fin', 'timbre', 'reference']
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

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once(__DIR__ . '/../includes/sidebar.php') ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php require_once(__DIR__ . '/../includes/topbar.php') ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div>
                        <h1 class="h4 mb-4 text-gray-800">Activités / <strong>Modification des informations</strong></h1>
                        <p class="mt-2">Ici, vous avez la main pour modifier toutes les informations que vous aviez enregistré pour votre activité.</p>
                    </div>


                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Formulaire de modification</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Messages d'erreurs divers -->
                                    <?php if (isset($errors['database'])): ?>
                                        <?php afficherAlerte('Erreur : ' . htmlspecialchars($errors['database']), 'danger') ?>
                                    <?php endif; ?>
                                    <?php if (isset($errors['duplicate'])): ?>
                                        <div class="alert alert-danger">
                                            <strong>Erreur :</strong> <?= htmlspecialchars($errors['duplicate']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (isset($doublon) && $doublon) : ?>
                                        <div class="alert alert-danger text-center alert-dismissible">
                                            Il semble que vous avez déjà créé une activité identique.
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Formulaire -->

                                    <form action="traitements/modifier_infos.php" method="post" id="activityForm" enctype="multipart/form-data">

                                        <!-- Hidden input for activity_id -->
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($activity_id) ?>">
                                        <!-- Rest of your form fields -->


                                        <!-- Informations générales -->
                                        <fieldset>
                                            <legend class="h6"><strong>Informations générales</strong></legend>
                                            <hr>
                                            <!-- Nom -->
                                            <div class="mb-2 row">
                                                <label for="nom" class="col-sm-3 col-form-label">Nom</label>
                                                <div class="col-sm-9">
                                                    <input id="nom" type="text" name="nom" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['nom']) ?>">
                                                    <small class="text-danger"><?= $errors['nom'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <div class="mb-2 row">
                                                <label for="description" class="col-sm-3 col-form-label">Description</label>
                                                <div class="col-sm-9">
                                                    <input id="description" type="text" name="description" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['description']) ?>">
                                                    <small class="text-danger"><?= $errors['description'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Centre -->
                                            <div class="mb-2 row">
                                                <label for="centre" class="col-sm-3 col-form-label">Centre</label>
                                                <div class="col-sm-9">
                                                    <input id="centre" type="text" name="centre" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['centre']) ?>">
                                                    <small class="text-danger"><?= $errors['centre'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Timbre de l'activité -->
                                            <div class="mb-4 row">
                                                <label for="timbre" class="col-sm-3 col-form-label">Timbre</label>
                                                <div class="col-sm-9">
                                                    <input id="timbre" type="text" name="timbre" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['timbre']) ?>">
                                                    <small class="text-danger"><?= $errors['timbre'] ?? '' ?></small>
                                                    <?= isset($errors['timbre']) ? '<br>' : '' ?>
                                                    <small> Note : Il doit être de la forme <strong>/DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD</strong></small>
                                                </div>
                                            </div>

                                            <!-- Référence -->
                                            <div class="mb-4 row">
                                                <label for="reference" class="col-sm-3 col-form-label">Référence</label>
                                                <div class="col-sm-9">
                                                    <input id="reference" type="text" name="reference" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['reference']) ?>">
                                                    <small class="text-danger"><?= $errors['reference'] ?? '' ?></small>
                                                    <?= isset($errors['reference']) ? '<br>' : '' ?>
                                                    <small> Note : Elle doit être de la forme <strong>0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA</strong></small>
                                                </div>
                                            </div>

                                            <!-- Premier responsable -->
                                            <div class="mb-2 row">
                                                <label for="premier_responsable" class="col-sm-3 col-form-label">Premier responsable</label>
                                                <div class="col-sm-9">
                                                    <input id="premier_responsable" type="text" name="premier_responsable" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['premier_responsable']) ?>">
                                                    <small class="text-danger"><?= $errors['premier_responsable'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Titre du responsable -->
                                            <div class="mb-2 row">
                                                <label for="titre_responsable" class="col-sm-3 col-form-label">Titre du responsable</label>
                                                <div class="col-sm-9">
                                                    <input id="titre_responsable" type="text" name="titre_responsable" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['titre_responsable']) ?>">
                                                    <small class="text-danger"><?= $errors['titre_responsable'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Organisateur -->
                                            <div class="mb-2 row">
                                                <label for="organisateur" class="col-sm-3 col-form-label">Organisateur</label>
                                                <div class="col-sm-9">
                                                    <input id="organisateur" type="text" name="organisateur" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['organisateur']) ?>">
                                                    <small class="text-danger"><?= $errors['organisateur'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Titre de l'orgnisateur -->
                                            <div class="mb-2 row">
                                                <label for="titre_organisateur" class="col-sm-3 col-form-label">Titre de l'organisateur </label>
                                                <div class="col-sm-9">
                                                    <input id="titre_organisateur" type="text" name="titre_organisateur" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['titre_organisateur']) ?>">
                                                    <small class="text-danger"><?= $errors['titre_organisateur'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Financier -->
                                            <div class="mb-2 row">
                                                <label for="financier" class="col-sm-3 col-form-label">Financier</label>
                                                <div class="col-sm-9">
                                                    <input id="financier" type="text" name="financier" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['financier']) ?>">
                                                    <small class="text-danger"><?= $errors['financier'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Titre du financier -->
                                            <div class="mb-2 row">
                                                <label for="titre_financier" class="col-sm-3 col-form-label">Titre du financier</label>
                                                <div class="col-sm-9">
                                                    <input id="titre_financier" type="text" name="titre_financier" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['titre_financier']) ?>">
                                                    <small class="text-danger"><?= $errors['titre_financier'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Diplômes -->
                                            <div class="mb-4 row">
                                                <label for="niveaux_diplome" class="col-sm-3 col-form-label">Diplômes</label>
                                                <div class="col-sm-9">
                                                    <input id="niveaux_diplome" type="text" name="niveaux_diplome" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['niveaux_diplome']) ?>">
                                                    <small class="text-danger"><?= $errors['niveaux_diplome'] ?? '' ?></small>
                                                    <?php if (isset($errors['niveaux_diplome'])) : ?>
                                                        <br>
                                                    <?php endif; ?>
                                                    <small> Note : séparés par des virgules, lettres accentuées autorisées, sans chiffres, ex : Licence,Master,Ingénieur (il s'agit de la liste des diplômes que les participants de l'activité ont. Les indiquer ici vous facilitera le travail quand vous devrez lier des participants à votre activité)</small>
                                                </div>
                                            </div>

                                            <!-- Titres associés -->
                                            <div class="mb-4 row">
                                                <label for="titres_associes" class="col-sm-3 col-form-label">Titres associés</label>
                                                <div class="col-sm-9">
                                                    <input id="titres_associes" type="text" name="titres_associes" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['titres_associes']) ?>">
                                                    <small class="text-danger"><?= $errors['titres_associes'] ?? '' ?></small>
                                                    <?php if (isset($errors['titres_associes'])) : ?>
                                                        <br>
                                                    <?php endif; ?>
                                                    <small> Note : séparés par des virgules, lettres uniquement, ex. : R/DEC,Superviseur (il s'agit des titres que les participants de l'activité auront. Tout comme dans le cas des diplômes, les indiquer ici vous facilitera le travail quand vous devrez lier des participants à votre activité)</small>
                                                    <!-- Avertissement -->
                                                    <small class="text-danger"> 
                                                        Avertissement : Si vous modifiez un titre pour lequel un ou des participants ont déjà été associés à votre activité, cette modification sera prise en compte dans les informations de la liaison avec ce ou ces participants.
                                                        Par-contre, si vous supprimez un titre pour lequel un ou des participants ont été associés à votre activité, la liaison avec ce ou ces participants sera rompue.
                                                    </small>
                                                </div>
                                            </div>
                                        </fieldset>

                                        <!-- Informations financières -->
                                        <fieldset class="mt-4">
                                            <legend class="h6"><strong>Informations financières</strong></legend>
                                            <hr>

                                            <!-- Champ spécifique aux types 1 et 2 -->
                                            <?php if (in_array($type_activite, [1, 2])) : ?>
                                                <!-- Taux journalier -->
                                                <div class="mb-2 row">
                                                    <label for="taux_journalier" class="col-sm-3 col-form-label">Taux journalier (FCFA)</label>
                                                    <div class="col-sm-9">
                                                        <input id="taux_journalier" type="text" name="taux_journalier" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['taux_journalier']) ?>">
                                                        <small class="text-danger"><?= $errors['taux_journalier'] ?? '' ?></small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Champs spécifiques aux types 2 et 3 -->
                                            <?php if (in_array($type_activite, [2, 3])) : ?>
                                                <!-- Indemnité(s) forfaitaire(s) -->
                                                <div class="mb-2 row">
                                                    <label for="indemnite_forfaitaire" class="col-sm-3 col-form-label">Indemnité(s) forfaitaire(s) (FCFA)</label>
                                                    <div class="col-sm-9">
                                                        <input id="indemnite_forfaitaire" type="text" name="indemnite_forfaitaire" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['indemnite_forfaitaire']) ?>">
                                                        <small class="text-danger"><?= $errors['indemnite_forfaitaire'] ?? '' ?></small>
                                                        <?= isset($errors['indemnite_forfaitaire']) ? '<br>' : '' ?>
                                                        <small class="text-muted">Note : séparés par des virgules, elles doivent être du même nombre que les titres, ex. : 100.50,200.75 (Chaque montant sera associé au titre en respectant l'ordre de saisie), renseignez 0 si un titre n'a pas d'indemnité</small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Champ spécifique au type 3 -->
                                            <?php if ($type_activite == 3) : ?>
                                                <!-- Taux par tâches -->
                                                <div class="mb-2 row">
                                                    <label for="taux_taches" class="col-sm-3 col-form-label">Taux par tâche (FCFA)</label>
                                                    <div class="col-sm-9">
                                                        <input id="taux_taches" type="text" name="taux_taches" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['taux_taches']) ?>">
                                                        <small class="text-danger"><?= $errors['taux_taches'] ?? '' ?></small>
                                                    </div>
                                                </div>
                                                <!-- Frais de déplacement -->
                                                <div class="mb-2 row">
                                                    <label for="frais_deplacement_journalier" class="col-sm-3 col-form-label">Frais de déplacement journaliers (FCFA)</label>
                                                    <div class="col-sm-9">
                                                        <input id="frais_deplacement_journalier" type="text" name="frais_deplacement_journalier" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['frais_deplacement_journalier']) ?>">
                                                        <small class="text-danger"><?= $errors['frais_deplacement_journalier'] ?? '' ?></small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </fieldset>

                                        <!-- Autres informations -->
                                        <fieldset class="mt-4">
                                            <legend class="h6"><strong>Autres informations</strong></legend>
                                            <hr>
                                            <!-- Note génératrice -->
                                            <!-- <div class="mb-2 row">
                                                <label for="note_generatrice" class="col-sm-3 col-form-label">Note génératrice <small class="text-muted">(fichier facultatif)</small></label>
                                                <div class="col-sm-9">
                                                    <input id="note_generatrice" type="file" name="note_generatrice" class="form-control">
                                                    <small class="text-danger"><?= $errors['note_generatrice'] ?? '' ?></small>
                                                </div>
                                            </div> -->

                                            <!-- Date de début -->
                                            <div class="mb-2 row">
                                                <label for="date_debut" class="col-sm-3 col-form-label">Date de début</label>
                                                <div class="col-sm-9">
                                                    <input id="date_debut" type="date" name="date_debut" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['date_debut']) ?>">
                                                    <small class="text-danger"><?= $errors['date_debut'] ?? '' ?></small>
                                                </div>
                                            </div>

                                            <!-- Date de fin -->
                                            <div class="mb-2 row">
                                                <label for="date_fin" class="col-sm-3 col-form-label">Date de fin</label>
                                                <div class="col-sm-9">
                                                    <input id="date_fin" type="date" name="date_fin" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['date_fin']) ?>">
                                                    <small class="text-danger"><?= $errors['date_fin'] ?? '' ?></small>
                                                </div>
                                            </div>
                                        </fieldset>

                                        <!-- Boutons d'action -->
                                        <div class="mt-4">
                                            <button class="btn btn-primary mr-3" id="submitButton" name="form_submitted" type="submit">Enregistrer les modifications</button>
                                            <a href="<?= $_SESSION['previous_url'] ?>" class="btn btn-outline-primary">Annuler</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php require_once(__DIR__ . '/../includes/footer.php') ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <?php require_once(__DIR__ . '/../includes/logoutModal.php') ?>
    <?php require_once(__DIR__ . '/../includes/scripts.php') ?>
</body>

</html>

</html>