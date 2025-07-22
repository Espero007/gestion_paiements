<?php
$section = 'Participants';
$titre_page = "Ajout de comptes bancaires";
require_once(__DIR__ . '/../includes/header.php');
$elements_a_inclure = ['infos_bancaires'];
require_once('includes/traitements_ajout_comptes.php');
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
                        <h1 class="h4 mb-4 text-gray-800">Acteurs / <strong>Ajout de comptes bancaires</strong></h1>
                        <?php if (isset($quota_comptes_bancaires_atteint) && $quota_comptes_bancaires_atteint) : ?>

                            <!-- On ne peut plus ajouter de comptes bancaires à ce participant -->
                            <p class="mt-2">Il semble que vous avez atteint le nombre maximal de comptes bancaires permis pour cet acteur (<?= NOMBRE_MAXIMAL_COMPTES ?>)</p>
                            <a href="gerer_participant.php?id=<?= $id_participant ?>">Annuler</a>

                        <?php elseif (isset($quota_comptes_bancaires_atteint) && !$quota_comptes_bancaires_atteint): ?>
                            <!-- On peut lui en ajouter -->

                            <?php if ($recuperer_nbr_comptes_bancaires) : ?>
                                <!-- Message à afficher quand on récupère le nombre de comptes qu'on doit lui ajouter -->
                                <?php $formatter = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT); ?>

                                <p class="mt-2"><strong><?= htmlspecialchars($nom) . ' ' . htmlspecialchars($prenoms) ?></strong> a déjà <?= $formatter->format($nombre_comptes_existants) ?><strong> (<?= $nombre_comptes_existants ?>)</strong> <?= $nombre_comptes_existants > 1 ? 'comptes bancaires enregistrés' : 'compte bancaire enregistré' ?> et ne peut en avoir que <?= $formatter->format(NOMBRE_MAXIMAL_COMPTES) ?> <strong>(<?= NOMBRE_MAXIMAL_COMPTES ?>)</strong> au total.</p>

                            <?php else: ?>
                                <!-- On a le nombre de comptes qu'on doit lui ajouter et donc on affiche le dernier message de la page -->
                                <p class="mt-2">Effectuez l'ajout.</p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (isset($quota_comptes_bancaires_atteint) && !$quota_comptes_bancaires_atteint) : ?>
                        <div class="row">
                            <?php if ($recuperer_nbr_comptes_bancaires) : ?>
                                <!-- Le quota bancaire n'est pas atteint donc on se retrouve dans deux, soit on prend le nombre de comptes bancaires intéressants soit on affiche le formulaire d'ajout des informations du/des compte(s) bancaires  -->

                                <div class="col-12">
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Formulaire d'ajout</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="" method="get">
                                                <div class="mb-2 row align-items-center">
                                                    <label for="nombre_comptes_bancaires" class="col-form-label col-sm-4">Nombre de comptes à ajouter</label>
                                                    <div class="col-sm-8">
                                                        <input type="hidden" name="id" value="<?= chiffrer($id_participant) ?>">
                                                        <input type="number" class="form-control" id="nombre_comptes_bancaires" name="nbr" placeholder="Indiquez le nombre" max="<?php echo $nombre_comptes_bancaires_permis; ?>" min="1" required>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary mt-2">Continuer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- On a le nombre de comptes bancaires à ajouter -->

                                <div class="col-12">
                                    <div class="card shadow mb-4">
                                        <div class="card-header py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Formulaire d'ajout</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="" method="post" enctype="multipart/form-data">
                                                <?php require_once('includes/fieldsets.php') ?>
                                                <input type="hidden" name="MAX_FILE_SIZE" value="<?= $taille_admissible_fichiers_pdf ?>">

                                                <!-- Boutons d'action -->
                                                <div class="mt-4 mb-4">
                                                    <button type="submit" name="ajouter_comptes" class="btn btn-primary mr-3">Ajouter <?= $nombre_comptes_bancaires > 1 ? 'les comptes' : 'le compte' ?></button>
                                                    <a href="gerer_participant.php?id=<?= $id_participant ?>" class="btn btn-outline-primary">Annuler</a>
                                                    <!-- <a href="/gestion_participants/ajouter_participant.php" class="btn btn-primary">Revenir à la page précédente</a> -->
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
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