<?php
$titre = 'Liaison Participant - Activité';
require_once('includes/header.php');
require_once('includes/traitements_lier_participant_activite.php');
?>
<!-- <link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css"> -->

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

                    <?php if (!isset($id_activite)) : ?>
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <h6 class="text-primary font-weight-bold">Etape 1</h6>
                            </div>

                            <div class="card-body">
                                <p>Sélectionnez l'activité.</p>
                                <form action="" method="get">
                                    <input type="hidden" name="id_participant" value="<?= $id_participant ?>">
                                    <div class="table-responsive text-no-wrap">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Choix</th>
                                                    <th>Nom</th>
                                                    <th>Période</th>
                                                    <th>Description</th>
                                                </tr>
                                            </thead>
                                            <tbody class="table-border-bottom-1">
                                                <?php foreach ($activites as $activite) : ?>
                                                    <tr>
                                                        <td><input type="checkbox" name="id_activite" value="<?= $activite['id'] ?>"></td>
                                                        <td><?= htmlspecialchars($activite['nom']) ?></td>
                                                        <td><?= htmlspecialchars(determinerPeriode($activite['date_debut'], $activite['date_fin'])) ?></td>
                                                        <td><?= htmlspecialchars(couperTexte($activite['description'], 18, 100)) ?></td>
                                                    </tr>


                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- <hr> -->
                                    <!-- Boutons d'actions -->
                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary mr-2">Continuer</button>
                                        <a href="gerer_participant.php?id=<?= $id_participant ?>" class="btn btn-outline-primary">Annuler</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php else: ?>


                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <h6 class="text-primary font-weight-bold">Etape 2</h6>
                            </div>
                            <div class="card-body">
                                <p>Sélectionnez le titre pour lequel le participant sera associé à l'activité et le compte bancaire qu'on devra considérer</p>
                                <form action="" method="post">
                                    <div class="mb-2 row">
                                        <label for="titre" class="col-form-label col-sm-4">Titre</label>
                                        <div class="col-sm-8">
                                            <select name="titre" id="titre" class="form-control">
                                                <option value="" selected>Choisissez le titre du participant...</option>
                                                <?php foreach ($titres as $titre) : ?>
                                                    <option value="<?= $titre['nom'] ?>"><?= htmlspecialchars($titre['nom']) ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                            <?php if (isset($erreurs['titre'])) : ?>
                                                <div class="form-text">
                                                    <small class="text-danger"><?= $erreurs['titre'] ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-2 row d-flex align-items-center">
                                        <label for="comptes_bancaires" class="col-form-label col-sm-4">Compte(s) bancaire(s)</label>
                                        <div class="col-sm-8">
                                            <div class="d-flex">
                                                <?php $index = 0; ?>
                                                <?php foreach ($comptes as $compte) : ?>
                                                    <?php $index++; ?>
                                                    <div class="form-check mr-4">
                                                        <input name="compte_bancaire" class="form-check-input" type="radio" value="<?= $compte['id'] ?>" id="compte<?= $index ?>">
                                                        <label class="form-check-label" for="compte<?= $index ?>"> <?= htmlspecialchars($compte['banque']) . ' (<i>' . htmlspecialchars($compte['numero_compte']) . '</i>)' ?></label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="form-text">
                                                <small class="text-danger">Bonjour</small>
                                            </div>
                                            <?php if (isset($erreurs['titre'])) : ?>
                                                <div class="form-text">
                                                    <small class="text-danger"><?= $erreurs['titre'] ?></small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Boutons d'action -->
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary mr-2" name="lier">Achever la liaison</button>
                                        <a href="voir_participants.php" class="btn btn-outline-primary">Annuler</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>


                    <!-- <div class="container-md pt-4">
                        <h5>Page de liaison d'un participant à une activité</h5>
                        <p>En cours de développement...</p>

                        <?php foreach ($activites as $activite) : ?>
                            <hr>
                            <div class="mb-4">
                                <p> <strong>Nom : </strong><?= $activite['nom'] ?></p>
                                <p> <strong>Description : </strong><?= $activite['description'] ?></p>
                                <p> <strong>Date_debut : </strong><?= $activite['date_debut'] ?></p>
                                <p> <strong>Date_fin : </strong><?= $activite['date_fin'] ?></p>
                                <p> <strong>Centre : </strong><?= $activite['centre'] ?></p>


                                <button class="btn btn-primary">Choisir</button>
                            </div>
                        <?php endforeach; ?>
                    </div> -->


                    <!-- 

    1- Récupérer la liste des activités créées par cet utilisateur
    2 - permettre le choix d'une activité
    3
    
    -->
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
        const submitBtn = document.querySelector('button[type=submit]');
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

        submitBtn.addEventListener('click', (e) => {
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