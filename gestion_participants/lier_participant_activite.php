<?php
$titre = 'Liaison Participant - Activité';
require_once('includes/header.php');
require_once('includes/traitements_lier_participant_activite.php');
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once('includes/sidebar.php') ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php require_once('includes/topbar.php') ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <h1 class="h4 mb-4 text-gray-800">Participants / <strong>Liaison Participant - Activité</strong></h1>
                    <p class="mt-2">Liez vos participants à vos activités pour profiter de toutes les fonctionnalités disponibles !</p>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">
                                <?= (isset($sens)) ? 'Etape 1' : 'Etape 2' ?>
                            </h6>
                        </div>

                        <div class="card-body">
                            <?php if (isset($sens)) : ?>
                                <!-- On est encore à l'étape 1 -->
                                <p>Sélectionnez <?= ($sens == 0) ? 'l\'activité.' : 'le participant.' ?></p>
                                <form action="" method="get">
                                    <input type="hidden" name="<?= ($sens == 0) ? 'id_participant' : 'id_activite' ?>" value="<?= ($sens == 0) ? $id_participant : $id_activite ?>">

                                    <div class="table">
                                        <table class="table">
                                            <?php if ($sens == 0) : ?>
                                                <!-- Participant vers activité -->
                                                <thead>
                                                    <tr>
                                                        <th>Choix</th>
                                                        <th>Nom</th>
                                                        <th>Période</th>
                                                        <th>Descritption</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($activites as $activite) : ?>
                                                        <tr>
                                                            <td><input type="checkbox" name="id_activite" value="<?= $activite['id'] ?>"></td>
                                                            <td><?= htmlspecialchars($activite['nom']) ?></td>
                                                            <td><?= htmlspecialchars(determinerPeriode($activite['date_debut'], $activite['date_fin'])) ?></td>
                                                            <td><?= htmlspecialchars(couperTexte($activite['description'], 13, 100)) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            <?php elseif ($sens == 1): ?>
                                                <!-- Activité vers participant -->
                                                <thead>
                                                    <tr>
                                                        <th>Choix</th>
                                                        <th>Nom</th>
                                                        <th>Prénom(s)</th>
                                                        <th>Matricule/IFU</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($participants as $participant) : ?>
                                                        <tr>
                                                            <td><input type="checkbox" name="id_participant" value="<?= $participant['id_participant'] ?>"></td>
                                                            <td><?= htmlspecialchars($participant['nom']) ?></td>
                                                            <td><?= htmlspecialchars($participant['prenoms']) ?></td>
                                                            <td><?= htmlspecialchars($participant['matricule_ifu']) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            <?php endif; ?>
                                        </table>
                                    </div>

                                    <!-- Boutons d'actions -->

                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary mr-2" id="submitBtn1">Continuer</button>
                                        <a href="gerer_participant.php?id=<?= $id_participant ?>" class="btn btn-outline-primary">Annuler</a>
                                    </div>
                                </form>
                            <?php else: ?>
                                <!-- Etape 2 -->
                                <p>Sélectionnez le titre pour lequel le participant sera associé à l'activité et le compte bancaire qu'on devra considérer</p>
                                <form action="" method="post">

                                    <!-- Titre -->
                                    <div class="mb-2 row">
                                        <label for="titre" class="col-form-label col-sm-4">Titre</label>
                                        <div class="col-sm-8">
                                            <select name="titre" id="titre" class="form-control <?= isset($erreurs['titre']) ? 'is-invalid' : '' ?>" aria-describdly=" titreAide">
                                                <option value="defaut" <?= (!isset($_POST['titre']) || !in_array($_POST['titre'], $titres_intitules)) ? 'selected' : '' ?>>Choisissez le titre du participant...</option>
                                                <?php foreach ($titres as $titre) : ?>
                                                    <option value="<?= $titre['nom'] ?>" <?= (isset($erreurs) && $titre['nom'] == $_POST['titre']) ? 'selected' : '' ?>><?= htmlspecialchars($titre['nom']) ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                            <?php if (isset($erreurs['titre'])) : ?>
                                                <div class="form-text" id="titreAide">
                                                    <small class="text-danger"><?= $erreurs['titre'][0] ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Compte bancaire -->
                                    <div class="mb-2 row">
                                        <span class="col-form-label col-sm-4">Compte(s) bancaire(s)</span>
                                        <div class="col-sm-8">
                                            <div class="d-flex col-form-label pb-0">
                                                <?php $index = 0; ?>
                                                <?php foreach ($comptes as $compte) : ?>
                                                    <?php $index++; ?>
                                                    <div class="form-check mr-4">
                                                        <input name="compte_bancaire" class="form-check-input" type="radio" value="<?= $compte['id'] ?>" id="compte<?= $index ?>" <?= (isset($_POST['compte_bancaire']) && $compte['id'] == $_POST['compte_bancaire'] && isset($erreurs)) ? 'checked' : '' ?> aria-describedby="compte_bancaireAide">
                                                        <label class="form-check-label" for="compte<?= $index ?>"> <?= htmlspecialchars($compte['banque']) . ' (<i>' . htmlspecialchars($compte['numero_compte']) . '</i>)' ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <?php if (isset($erreurs['compte_bancaire'])) : ?>
                                                <div class="form-text" id="compte_bancaireAide">
                                                    <small class="text-danger"><?= $erreurs['compte_bancaire'][0] ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($type_activite != 1) : ?>
                                        <!-- Champs additionnels -->
                                        <?php $champs = [
                                            'nbr_jours' => 'Nombre de jours de travail',
                                            'nbr_taches' => 'Nombre de tâches'
                                        ]
                                        ?>
                                        <?php foreach ($champs as $champ => $label) : ?>
                                            <div class="mb-2 row">
                                                <label for="<?= $champ ?>" class="col-form-label col-sm-4"><?= $label ?></label>
                                                <div class="col-sm-8">
                                                    <input type="number" name="<?= $champ ?>" id="<?= $champ ?>" class="form-control <?= isset($erreurs[$champ]) ? 'is-invalid' : '' ?> " aria-describedby="<?= $champ ?>Aide" <?= (isset($erreurs) && isset($_POST[$champ])) ? 'value ="' . htmlspecialchars($_POST[$champ]) . '"' : '' ?> placeholder="Indiquez le <?= strtolower($label) ?>">
                                                    <?php if (isset($erreurs[$champ])) : ?>
                                                        <div class="form-text" id="<?= $champ ?>Aide">
                                                            <small class="text-danger"><?= $erreurs[$champ][0] ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <!-- Boutons d'action -->
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary mr-2" name="lier">Achever la liaison</button>
                                        <a href="voir_participants.php" class="btn btn-outline-primary">Annuler</a>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php require_once('includes/footer.php') ?>
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
    <?php require_once('includes/logoutModal.php') ?>
    <?php require_once('includes/scripts.php') ?>

    <script>
        let cbxes = document.querySelectorAll('input[type=checkbox]');
        const submitBtn1 = document.querySelector('#submitBtn1'); // Le bouton pour choisir l'activité
        // submitBtn.disabled = true;
        // console.log(submitBtn);

        cbxes.forEach(cbx => {
            cbx.addEventListener('change', (e) => {
                if (e.target.checked)
                    uncheckOthers(e.target);
            })
        })

        function uncheckOthers(clicked) {
            cbxes.forEach(other => {
                if (other != clicked)
                    other.checked = false;
            })
        }

        let submit = false;

        submitBtn1.addEventListener('click', (e) => {
            cbxes.forEach(cbx => {
                if (cbx.checked == true)
                    submit = true;
            })
            if (!submit)
                e.preventDefault();
        })
    </script>
</body>

</html>