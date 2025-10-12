<?php
$section = 'Activités';
$titre_page = "Création d'une activité";
require_once(__DIR__ . '/../includes/header.php');
require_once('traitements/submit_creer_activite.php');
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
                        <h1 class="h4 mb-4 text-gray-800"> Activités /
                            <strong>Création d'une activité <?= isset($type_activite) ? 'de type ' . $type_activite : '' ?></strong>
                        </h1>
                        <p class="mt-2">Vous êtes sur le point de créer une activité. Nous allons vous guider tout au long du processus.</p>
                    </div>

                    <?php if (isset($recuperation_type_activite) && !$recuperation_type_activite) : ?>
                        <!-- Sélection du type d'activité -->
                        <p class="mt-2">Commencez par nous dire quel type d'activité vous aimeriez créer.</p>
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
                        <!-- Formulaire de création -->
                        <p class="mt-2">Dîtes-nous en plus sur votre activité en renseignant le formulaire ci-dessous</p>

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
                                                <div class="mb-2 row">
                                                    <div class="form-group col-12">
                                                        <label for="titres">Titres associés</label>
                                                        <div class="titres-container">
                                                            <?php
                                                            if (!empty($titres_apres)) {
                                                                foreach ($titres_apres as $index => $t) {
                                                                    $titre_val = htmlspecialchars($t['nom']);
                                                                    $indem_val = in_array($type_activite, ['2','3']) ? htmlspecialchars($t['indemnite_forfaitaire']) : '';
                                                                    echo '<div class="titre-item">';
                                                                    echo '<input type="text" name="titres[]" class="form-control titre-input" value="'.$titre_val.'" placeholder="Titre">';
                                                                    if (in_array($type_activite, ['2','3'])) {
                                                                        echo '<input type="number" step="0.01" name="indemnites[]" class="form-control indem-input" value="'.$indem_val.'" placeholder="Indemnité">';
                                                                    }
                                                                    echo '<button type="button" class="btn btn-outline-primary remove-titre">Supprimer</button>';
                                                                    echo '</div>';
                                                                }
                                                            }
                                                            ?>
                                                        </div>

                                                        <button type="button" id="add-titre" class="btn btn-primary mr-3 mt-2">Ajouter un titre</button>
                                                        
                                                    </div>
                                                </div>

                                                <input type="hidden" name="titres_associes" id="titres_associes" value="<?= htmlspecialchars($data['titres_associes'] ?? '') ?>">
                                                <?php if(in_array($type_activite, ['2', '3'])): ?>
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

                                            <!-- ================= JS DYNAMIQUE TITRES ================= -->
                                            <script>
                                                const container = document.querySelector('.titres-container');
                                                const addBtn = document.getElementById('add-titre');
                                                const typeActivite = <?= json_encode($type_activite); ?>;

                                                function createTitre(titre = '', indem = '') {
                                                    const div = document.createElement('div');
                                                    div.classList.add('titre-item');

                                                    const titreInput = document.createElement('input');
                                                    titreInput.type = 'text';
                                                    titreInput.name = 'titres[]';
                                                    titreInput.classList.add('form-control', 'titre-input');
                                                    titreInput.placeholder = 'Titre';
                                                    titreInput.value = titre;
                                                    div.appendChild(titreInput);

                                                    if (typeActivite === '2' || typeActivite === '3') {
                                                        const indemInput = document.createElement('input');
                                                        indemInput.type = 'number';
                                                        indemInput.step = '0.01';
                                                        indemInput.name = 'indemnites[]';
                                                        indemInput.classList.add('form-control', 'indem-input');
                                                        indemInput.placeholder = 'Indemnité';
                                                        indemInput.value = indem;
                                                        div.appendChild(indemInput);
                                                    }

                                                    const removeBtn = document.createElement('button');
                                                    removeBtn.type = 'button';
                                                    removeBtn.classList.add('btn', 'btn-outline-primary');
                                                    removeBtn.textContent = 'Supprimer';
                                                    removeBtn.addEventListener('click', () => div.remove());
                                                    div.appendChild(removeBtn);

                                                    return div;
                                                }

                                                addBtn.addEventListener('click', () => {
                                                    container.appendChild(createTitre());
                                                });

                                                const form = document.querySelector('form');
                                                form.addEventListener('submit', e => {
                                                    const titres = document.querySelectorAll('.titre-input');
                                                    const indemnites = document.querySelectorAll('.indem-input');

                                                    let valid = false;
                                                    titres.forEach((t, i) => {
                                                        if (t.value.trim() !== '' || (indemnites[i] && indemnites[i].value.trim() !== '')) {
                                                            valid = true;
                                                        }
                                                    });

                                                    if (!valid) {
                                                        e.preventDefault();
                                                        alert('Veuillez entrer au moins un titre et/ou une indemnité.');
                                                    }
                                                });

                                                if (container.children.length === 0) {
                                                    container.appendChild(createTitre());
                                                }
                                            </script>

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

</body>
</html>
