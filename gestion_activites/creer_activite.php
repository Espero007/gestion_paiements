<?php
$section = 'Activités';
$titre_page = "Création d'une activité";
require_once(__DIR__ . '/../includes/header.php');
require_once('traitements/submit_creer_activite.php');
?>

<body id="page-top">
    <style>
        a:hover.no-decoration {
            text-decoration: none;
        }
    </style>

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
                        <h1 class="h4 mb-4 text-gray-800"> Activités /
                            <strong>Création d'une activité <?= isset($type_activite) ? 'de type ' . $type_activite : '' ?></strong>
                        </h1>
                        <p class="mt-2">Vous êtes sur le point de créer une activité. Nous allons vous guider tout au long du processus.</p>
                        <!-- <hr> -->
                    </div>

                    <?php if (isset($recuperation_type_activite) && !$recuperation_type_activite) : ?>
                        <!-- Le type de l'activité n'a pas encore été choisi -->
                        <p class="mt-2">Commencez par nous dire quel type d'activité vous aimeriez créer. Nous disposons de trois types qui ont chacun leurs particularités.</p>

                        <!-- Content Row -->
                        <div class="row mb-5">
                            <div class="col-md-6 col-lg-4">
                                <div class="card text-center shadow">
                                    <div class="card-body">
                                        <div class="card-title font-weight-bold text-primary">Type 1</div>
                                        <p class="card-text">Ce genre d'activité est défini par le montant par jour et le nombre de jours de travail.</p>
                                        <a href="creer_activite.php?type_activite=1" class="btn btn-primary">Créer ce type d'activité</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="card text-center shadow">
                                    <div class="card-body">
                                        <div class="card-title font-weight-bold text-primary">Type 2</div>
                                        <p class="card-text">Ce genre d'activité est défini par le montant par jour, le nombre de jours de travail et une indemnité forfaitaire pour certains acteurs.</p>
                                        <a href="creer_activite.php?type_activite=2" class="btn btn-primary">Créer ce type d'activité</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <div class="card text-center shadow">
                                    <div class="card-body">
                                        <div class="card-title font-weight-bold text-primary">Type 3</div>
                                        <p class="card-text">Ce genre d'activité est défini par le nombre de tâches, le montant par tâche, les frais de déplacement par jour, le nombre de jours de travail et un forfait pour certains acteurs.</p>
                                        <a href="creer_activite.php?type_activite=3" class="btn btn-primary">Créer ce type d'activité</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif (isset($recuperation_type_activite) && $recuperation_type_activite): ?>
                        <!-- Le type de l'activité a été récupéré et est valide -->
                        <p class="mt-2">Dîtes-nous en plus sur votre activité en renseignant le formulaire ci-dessous</p>

                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Formulaire de création</h6>
                                    </div>
                                    <div class="card-body">
                                        <!-- Messages d'erreur divers -->
                                        <?php if (isset($doublon) && $doublon) : ?>
                                            <div class="alert alert-danger text-center alert-dismissible">
                                                Il semble que vous avez déjà créé une activité identique.
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Formulaire -->
                                        <form action="" method="post" enctype="multipart/form-data" id="activityForm">
                                            <!-- <input type="hidden" name="form_submitted" value="1"> -->

                                            <!-- Informations générales -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start mt-0">
                                                        <div class="divider-text"><strong>Informations générales</strong></div>
                                                    </div>
                                                </legend>
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
                                                        <div class="input-group">
                                                            <div class="input-group-text d-flex align-items-end"><span>N°</span>
                                                                <hr class="m-0" style="width : 40px;">
                                                            </div>
                                                            <input id="timbre" type="text" name="timbre" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['timbre']) ?>">
                                                        </div>
                                                        <small class="text-danger"><?= $errors['timbre'] ?? '' ?></small>
                                                        <?= isset($errors['timbre']) ? '<br>' : '' ?>
                                                        <small> Note : Il doit être de la forme <strong>N°
                                                                <hr class="m-0 d-inline-block" style="width : 40px;"> /DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD
                                                            </strong></small>
                                                    </div>
                                                </div>

                                                <!-- Référence -->
                                                <div class="mb-4 row">
                                                    <label for="reference" class="col-sm-3 col-form-label">Référence</label>
                                                    <div class="col-sm-9">
                                                        <input id="reference" type="text" name="reference" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['reference']) ?>">
                                                        <small class="text-danger"><?= $errors['reference'] ?? '' ?></small>
                                                        <?= isset($errors['reference']) ? '<br>' : '' ?>
                                                        <small> Note : Elle doit être de la forme <strong>NS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA du 24 août 2023 </strong></small>
                                                    </div>
                                                </div>

                                                 <!-- Mode de Paiyement -->
                                                <div class="mb-4 row">
                                                    <label for="mode_payement" class="col-sm-3 col-form-label">Mode de payement</label>
                                                    <div class="col-sm-9">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="mode_payement" id="mode_payement_nouveau" value="1"
                                                                <?= ($success ? '' : ($data['mode_payement'] === '1' ? 'checked' : '')) ?>>
                                                            <label class="form-check-label" for="mode_payement_nouveau">Nouveau</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="mode_payement" id="mode_payement_ancien" value="0"
                                                                <?= ($success ? '' : ($data['mode_payement'] === '0' ? 'checked' : '')) ?>>
                                                            <label class="form-check-label" for="mode_payement_ancien">Ancien</label>
                                                        </div>
                                                        <small class="text-danger"><?= $errors['mode_payement'] ?? '' ?></small>
                                                        <small><br> Sélectionner <strong>Nouveau</strong> si les informations financières de l'utilisateur sont liés à sont compte bancaire. Sinon sélectionnez <strong>Ancien</strong>.</small>
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
                                                <!-- <div class="mb-4 row">
                                                    <label for="niveaux_diplome" class="col-sm-3 col-form-label">Diplômes</label>
                                                    <div class="col-sm-9">
                                                        <input id="niveaux_diplome" type="text" name="niveaux_diplome" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['niveaux_diplome']) ?>">
                                                        <small class="text-danger"><?= $errors['niveaux_diplome'] ?? '' ?></small>
                                                        <?php if (isset($errors['niveaux_diplome'])) : ?>
                                                            <br>
                                                        <?php endif; ?>
                                                        <small> Note : séparés par des virgules, lettres accentuées autorisées, sans chiffres, ex : Licence,Master,Ingénieur (il s'agit de la liste des diplômes que les participants de l'activité ont. Les indiquer ici vous facilitera le travail quand vous devrez lier des participants à votre activité)</small>
                                                    </div>
                                                </div> -->

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
                                                    </div>
                                                </div>

                                            </fieldset>

                                            <!-- Informations financières -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start mt-0">
                                                        <div class="divider-text"><strong>Informations financières</strong></div>
                                                    </div>
                                                </legend>

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
                                                            <small class="text-muted">Note : séparés par des virgules, elles doivent être du même nombre que les titres, ex. : 100.50,200.75 (chaque montant indiqué sera associé au titre correspondant en respectant l'ordre de saisie), renseignez 0 si un titre n'a pas d'indemnité</small>
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
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start mt-0">
                                                        <div class="divider-text"><strong>Autres informations</strong></div>
                                                    </div>
                                                </legend>

                                                <!-- Note génératrice -->
                                                <!-- <div class="mb-2 row">
                                                    <label for="note_generatrice" class="col-sm-3 col-form-label">Note génératrice <small class="text-muted">(fichier, obligatoire)</small></label>
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
                                                <button class="btn btn-primary mr-3" id="submitButton" type="submit" name="form_submitted">Créer l'activité</button>
                                                <a href="creer_activite.php" class="btn btn-outline-primary">Annuler</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>


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


    <!-- <script>
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
    </script> -->
</body>

</html>