<?php
$titre = "Ajout de comptes bancaires";
require_once('includes/header.php');
$elements_a_inclure = ['infos_bancaires'];
require_once(__DIR__ . '/includes/traitements_ajout_comptes.php');
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
                    <div>
                        <h1 class="h3 mb-4 text-gray-800">
                            Ajout de comptes bancaires
                        </h1>
                        <?php if (isset($quota_comptes_bancaires_atteint) && $quota_comptes_bancaires_atteint) : ?>
                            <p class="mt-2">Il semble que vous avez atteint le nombre maximal de comptes bancaires permis pour ce participant (<?= NOMBRE_MAXIMAL_COMPTES; ?>)</p>
                            <a href="/gestion_participants/ajouter_participant.php">Revenir en arrière</a>
                        <?php elseif (isset($quota_comptes_bancaires_atteint) && !$quota_comptes_bancaires_atteint): ?>
                            <?php if ($recuperer_nbr_comptes_bancaires) : ?>
                                <p class="mt-2">Dîtes-nous, combien de comptes bancaires voulez-vous ajouter à <strong><?= htmlspecialchars($nom) . ' ' . htmlspecialchars($prenoms) ?></strong> ? Il en a déjà (<strong><?= $nombre_comptes_existants ?></strong>) et ne peut en avoir que (<strong><?= NOMBRE_MAXIMAL_COMPTES ?></strong>) au total.</p>
                            <?php else: ?>
                                <p class="mt-2">Effectuez l'ajout des comptes bancaires</p>
                            <?php endif; ?>
                            <hr>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($quota_comptes_bancaires_atteint) && !$quota_comptes_bancaires_atteint) : ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Formulaire d'ajout</h6>
                                    </div>
                                    <div class="card-body">
                                        <?php //Le quota bancaire n'est pas atteint donc on se retrouve dans deux, soit on prend le nombre de comptes bancaires intéressants soit on affiche le formulaire d'ajout des informations du/des compte(s) bancaires 
                                        ?>
                                        <?php if ($recuperer_nbr_comptes_bancaires) : ?>

                                            <form action="" method="get">
                                                <div class="mb-2 row">
                                                    <label for="nombre_comptes_bancaires" class="col-sm-5 col-form-label">Nombre de comptes à ajouter</label>
                                                    <div class="col-sm-7">
                                                        <input type="hidden" name="id_participant" value="<?= $id_participant ?>">
                                                        <input type="number" class="form-control" id="nombre_comptes_bancaires" name="nombre_comptes_bancaires" placeholder="Indiquez le nombre" max="<?php echo $nombre_comptes_bancaires_permis; ?>" min="1" required>
                                                    </div>
                                                </div>
                                                <button type="submit" class="btn btn-primary mt-2">Continuer</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="" method="post" enctype="multipart/form-data">
                                                <?php require_once('includes/fieldsets.php') ?>
                                                <input type="hidden" name="MAX_FILE_SIZE" value="<?= $taille_admissible_fichiers_pdf ?>">
                                                <button type="submit" name="ajouter_comptes" class="btn btn-primary mt-4 mb-3">Ajouter le(s) compte(s)</button>

                                                <div class="mb-4">
                                                    <!-- Liens additionnels -->
                                                    <a href="/gestion_participants/ajouter_comptes.php?id_participant=<?= $id_participant ?>" class="btn btn-primary">Changer le nombre de comptes</a>
                                                    <!-- <br> -->
                                                    <a href="/gestion_participants/ajouter_participant.php" class="btn btn-primary">Revenir à la page précédente</a>
                                                </div>
                                            </form>
                                        <?php endif; ?>
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
</body>

</html>