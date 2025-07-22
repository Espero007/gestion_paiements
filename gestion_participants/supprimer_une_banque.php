<?php
$section = 'Participants';
$titre_page = "Suppression Compte Bancaire";
require_once(__DIR__ . '/../includes/header.php');
require_once('includes/suppression_banque.php');
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
                    <h1 class="h4 mb-2 text-gray-800">Acteurs / <strong>Suppression de compte(s)</strong></h1>
                    <p class="mt-2">Sélectionnez le compte bancaire associé à l'acteur que vous souhaitez supprimer puis achevez la suppression</p>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Comptes</h6>
                        </div>
                        <div class="card-body pt-0">
                            <!-- Messages divers -->
                            <?php if (isset($erreurs['pas_de_choix'])) : ?>
                                <?php afficherAlerte('Sélectionnez un compte bancaire à supprimer', 'danger') ?>
                            <?php endif; ?>
                            <?php if (isset($erreurs['trop_de_comptes'])) : ?>
                                <?php afficherAlerte('Vous ne pouvez pas supprimer tous les comptes du participant', 'danger') ?>
                            <?php endif; ?>
                            <?php if (isset($erreurs['compte_deja_utilise'])) : ?>
                                <?php afficherAlerte($erreurs['compte_deja_utilise'], 'info') ?>
                            <?php endif; ?>
                            <!-- Fin messages divers -->

                            <div class="divider text-start">
                                <div class="divider-text"><strong>Faîtes un choix</strong></div>
                            </div>

                            <form action="" method="post">
                                <?php foreach ($banques as $banque) : ?>
                                    <div class="form-check mt-3">
                                        <input type="checkbox" name="banque[]" id="<?= $banque['numero_compte'] ?>" value="<?= $banque['numero_compte'] ?>" class="form-check-input" <?= isset($erreurs) ? (isset($_POST['banque']) && in_array($banque['numero_compte'], $_POST['banque']) ? 'checked' : '') : '' ?>>
                                        <label for="<?= $banque['numero_compte'] ?>" class="form-check-label"><?= htmlspecialchars($banque['banque'] . ' (' . strtoupper($banque['numero_compte'])) . ')' ?></label>
                                    </div>
                                <?php endforeach; ?>

                                <div class="divider text-start">
                                    <div class="divider-text"><strong>Action</strong></div>
                                </div>

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name='suppressionBanque' id='suppressionBanque' value="yes" <?= isset($erreurs) ? (isset($_POST['suppressionBanque']) ? 'checked' : '') : '' ?>>
                                    <label for="suppressionBanque" class="form-check-label">Je confirme la suppression</label>
                                    <?php if (isset($erreurs['confirmation'])) : ?>
                                        <p class="m-0"><small class="text-danger">Veuillez confirmer la suppression de votre compte</small></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                    <a href="<?= $_SESSION['previous_url'] ?>" class="btn btn-secondary ml-2">Annuler</a>
                                </div>
                            </form>
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