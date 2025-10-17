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
                        <p class="mt-2 mb-1">Vous êtes sur le point de créer une activité. Nous allons vous guider tout au long du processus.</p>
                        <!-- <hr> -->
                    </div>

                    <?php if (isset($recuperation_type_activite) && !$recuperation_type_activite) : ?>
                        <!-- Le type de l'activité n'a pas encore été choisi -->
                        <p class="mt-0">Commencez par nous dire quel type d'activité vous aimeriez créer. Nous disposons de trois types qui ont chacun leurs particularités.</p>

                        <!-- Content Row -->

                        <div class="row mb-5">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card text-center shadow">
                                        <div class="card-body">
                                            <div class="card-title font-weight-bold text-primary">Type <?= $i ?></div>
                                            <p class="card-text">
                                                <?php
                                                if ($i === 1) echo "Montant par jour et nombre de jours de travail.";
                                                elseif ($i === 2) echo "Montant par jour, nombre de jours et indemnité forfaitaire pour certains acteurs.";
                                                else echo "Nombre de tâches, montant par tâche, frais de déplacement, nombre de jours et forfait pour certains acteurs.";
                                                ?>
                                            </p>
                                            <a href="creer_activite.php?type_activite=<?= $i ?>" class="btn btn-primary">Créer ce type d'activité</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>

                    <?php elseif (isset($recuperation_type_activite) && $recuperation_type_activite): ?>
                        <!-- Le type de l'activité a été récupéré et est valide -->
                        <p class="mt-0">Dîtes-nous en plus sur votre activité en renseignant le formulaire ci-dessous</p>
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Formulaire de création</h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- Messages d'erreur doublon -->
                                        <?php if (isset($doublon) && $doublon) : ?>
                                            <div class="alert alert-danger text-center alert-dismissible">
                                                Il semble que vous avez déjà créé une activité identique.
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Formulaire -->
                                        <form action="" method="post" enctype="multipart/form-data" id="activityForm">

                                            <!-- ================= INFORMATIONS GENERALES ================= -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start mt-0">
                                                        <div class="divider-text"><strong>Informations générales</strong></div>
                                                    </div>
                                                </legend>

                                                <!-- Nom, Description, Centre, Timbre, etc. -->
                                                <?php
                                                $champs = [
                                                    'nom' => 'Nom',
                                                    'description' => 'Description',
                                                    'centre' => 'Centre',
                                                    'timbre' => 'Timbre',
                                                    'reference' => 'Référence',
                                                    'premier_responsable' => 'Premier responsable',
                                                    'titre_responsable' => 'Titre du responsable',
                                                    'organisateur' => 'Organisateur',
                                                    'titre_organisateur' => 'Titre de l\'organisateur',
                                                    'financier' => 'Financier',
                                                    'titre_financier' => 'Titre du financier'
                                                ];
                                                foreach ($champs as $key => $label):
                                                ?>
                                                    <div class="mb-2 row">
                                                        <label for="<?= $key ?>" class="col-sm-3 col-form-label"><?= $label ?></label>
                                                        <div class="col-sm-9">
                                                            <input id="<?= $key ?>" type="text" name="<?= $key ?>" class="form-control" value="<?= $success ? '' : htmlspecialchars($data[$key]) ?>">
                                                            <small class="text-danger"><?= $errors[$key] ?? '' ?></small>
                                                        </div>
                                                    </div>                                               
                                                <?php endforeach; ?>

                                                <!-- =================== TITRES DYNAMIQUES =================== -->
                                                <div class="row">
                                                        <label for="titres"  class="col-sm-3 col-form-label">Titres associés</label>
                                                        <div class="col-sm-9">
                                                        <div id="titres-container">
                                                            <?php
                                                            if (!empty($titres_apres)) {
                                                                foreach ($titres_apres as $index => $t) {
                                                                    $titre_val = htmlspecialchars($t['nom']);
                                                                    $indem_val = in_array($type_activite, ['2', '3']) ? htmlspecialchars($t['indemnite_forfaitaire']) : '';
                                                                    echo '<div class="titre-item mb-2 d-flex gap-2 align-items-center">';
                                                                    echo '<input type="text" name="titres[]" class="form-control titre-input" value="' . $titre_val . '" placeholder="Titre">';
                                                                    if (in_array($type_activite, ['2', '3'])) {
                                                                        echo '<div class="input-group">';
                                                                        echo '<input type="number" step="0.01" name="indemnites[]" class="form-control indem-input" value="' . $indem_val . '" placeholder="Indemnité">';
                                                                        echo '<span class="input-group-text">FCFA</span>';
                                                                        echo '</div>';
                                                                    }
                                                                    echo '<button type="button" class="btn btn-outline-danger remove-titre">Supprimer</button>';
                                                                    echo '</div>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>
                                                    <!-- Bouton pour ajouter dynamiquement un nouveau titre -->
                                                    <div class="d-flex justify-content-center">
                                                        <button type="button" id="add-titre" class="btn btn-outline-primary mt-2">
                                                            Ajouter un titre
                                                        </button>
                                                    </div>

                                                    <div>
                                                        <small class="text-danger"><?= $errors['titres_associes'] ?? '' ?></small>
                                                    </div>

                                                    </div>
                                                </div>

                                                <input type="hidden" name="titres_associes" id="titres_associes" value="<?= htmlspecialchars($data['titres_associes'] ?? '') ?>">
                                                <?php if (in_array($type_activite, ['2', '3'])): ?>
                                                    <input type="hidden" name="indemnite_forfaitaire" id="indemnite_forfaitaire" value="<?= htmlspecialchars($data['indemnite_forfaitaire'] ?? '') ?>">
                                                <?php endif; ?>
                                            </fieldset>

                                            <!-- ================= INFORMATIONS FINANCIERES ================= -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start mt-0">
                                                        <div class="divider-text"><strong>Informations financières</strong></div>
                                                    </div>
                                                </legend>

                                                <?php if (in_array($type_activite, [1, 2])): ?>
                                                    <div class="mb-2 row">
                                                        <label for="taux_journalier" class="col-sm-3 col-form-label">Taux journalier (FCFA)</label>
                                                        <div class="col-sm-9">
                                                            <input id="taux_journalier" type="text" name="taux_journalier" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['taux_journalier']) ?>">
                                                            <small class="text-danger"><?= $errors['taux_journalier'] ?? '' ?></small>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($type_activite == 3): ?>
                                                    <div class="mb-2 row">
                                                        <label for="taux_taches" class="col-sm-3 col-form-label">Taux par tâche (FCFA)</label>
                                                        <div class="col-sm-9">
                                                            <input id="taux_taches" type="text" name="taux_taches" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['taux_taches']) ?>">
                                                            <small class="text-danger"><?= $errors['taux_taches'] ?? '' ?></small>
                                                        </div>
                                                    </div>
                                                    <div class="mb-2 row">
                                                        <label for="frais_deplacement_journalier" class="col-sm-3 col-form-label">Frais de déplacement journaliers (FCFA)</label>
                                                        <div class="col-sm-9">
                                                            <input id="frais_deplacement_journalier" type="text" name="frais_deplacement_journalier" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['frais_deplacement_journalier']) ?>">
                                                            <small class="text-danger"><?= $errors['frais_deplacement_journalier'] ?? '' ?></small>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                 <!-- Mode de Paiyement -->
                                            <div class="mb-4 row">
                                                <label for="mode_payement" class="col-sm-3 col-form-label">Mode de payement</label>
                                                <div class="col-sm-9">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="mode_payement" id="mode_payement_nouveau" value="1">
                                                        <label class="form-check-label" for="mode_payement_nouveau">Nouveau</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="mode_payement" id="mode_payement_ancien" value="0">
                                                        <label class="form-check-label" for="mode_payement_ancien">Ancien</label>
                                                    </div>
                                                    <small class="text-danger"><?= $errors['mode_payement'] ?? '' ?></small>
                                                    <small><br> Sélectionnez <strong>Nouveau</strong> si les informations financières de l'utilisateur sont liés à sont compte bancaire. Sinon sélectionnez <strong>Ancien</strong>.</small>

                                                </div>
                                            </div>

                                            </fieldset>

                                            <!-- ================= AUTRES INFORMATIONS ================= -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start mt-0">
                                                        <div class="divider-text"><strong>Autres informations</strong></div>
                                                    </div>
                                                </legend>

                                                <div class="mb-2 row">
                                                    <label for="date_debut" class="col-sm-3 col-form-label">Date de début</label>
                                                    <div class="col-sm-9">
                                                        <input id="date_debut" type="date" name="date_debut" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['date_debut']) ?>">
                                                        <small class="text-danger"><?= $errors['date_debut'] ?? '' ?></small>
                                                    </div>
                                                </div>

                                                <div class="mb-2 row">
                                                    <label for="date_fin" class="col-sm-3 col-form-label">Date de fin</label>
                                                    <div class="col-sm-9">
                                                        <input id="date_fin" type="date" name="date_fin" class="form-control" value="<?= $success ? '' : htmlspecialchars($data['date_fin']) ?>">
                                                        <small class="text-danger"><?= $errors['date_fin'] ?? '' ?></small>
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <!-- ================= BOUTONS ================= -->
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
    
    <!-- ================= JS DYNAMIQUE TITRES ================= -->
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeActivite = '<?= $type_activite ?>';
            const container = document.getElementById('titres-container');
            const addBtn = document.getElementById('add-titre');
            const hiddenTitres = document.getElementById('titres_associes');
            const hiddenIndems = document.getElementById('indemnite_forfaitaire');

            function getValues() {
                const titres = Array.from(container.querySelectorAll('.titre-input')).map(i => i.value.trim());
                const indems = Array.from(container.querySelectorAll('.indem-input')).map(i => i.value.trim());
                return {
                    titres,
                    indems
                };
            }

            function syncHidden() {
                const {
                    titres,
                    indems
                } = getValues();
                hiddenTitres.value = titres.filter(t => t !== '').join(',');
                if (hiddenIndems) hiddenIndems.value = indems.join(',');
            }

            // Ajouter dynamiquement un titre
            addBtn.addEventListener('click', () => {
                const div = document.createElement('div');
                div.className = 'titre-item mb-2 d-flex gap-2 align-items-center appear';
                div.innerHTML = `
                <input type="text" name="titres[]" class="form-control titre-input" placeholder="Titre">
                ${(typeActivite === '2' || typeActivite === '3') 
                    ? '<div class="input-group"><input type="number" step="0.01" name="indemnites[]" class="form-control indem-input" placeholder="Indemnité"><span class="input-group-text">FCFA</span></div>' 
                    : ''}
                <button type="button" class="btn btn-outline-danger remove-titre">Supprimer</button>
            `;

                // On ajoute l'élément
                container.appendChild(div);

                // On retire la classe d'animation
                setTimeout(() => {
                    document.querySelectorAll('div').forEach(div => {
                        if (div.classList.contains('appear')) div.classList.remove('appear');
                    });
                }, 400);
            });

            // Supprimer un titre
            container.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-titre')) {
                    const element = e.target.closest('.titre-item');
                    element.classList.add('desappear');

                    setTimeout(() => {
                        element.remove();
                    }, 400);

                    syncHidden();
                }
            });

            // Synchroniser à chaque saisie
            container.addEventListener('input', syncHidden);

            // Vérification avant envoi
            const form = document.querySelector('#activityForm');
            form.addEventListener('submit', function(e) {
                syncHidden();
                const {
                    titres,
                    indems
                } = getValues();
                const hasTitre = titres.some(t => t !== '');
                const hasIndem = (typeActivite === '2' || typeActivite === '3') ?
                    indems.some(i => i !== '') :
                    true;

                    /*
                if (!hasTitre || !hasIndem) {
                    e.preventDefault();
                    alert('Veuillez saisir au moins un titre (et une indemnité si nécessaire).');
                } */
            });

            //  Initial sync
            syncHidden();
        });
    </script>
</body>

</html>