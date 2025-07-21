<?php
$section = 'Participants';
$titre_page = "Ajout d'un acteur";
require_once(__DIR__ . '/../includes/header.php');
require_once('includes/traitements_ajout_participant.php');
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
                        <h1 class="h4 mb-4 text-gray-800">
                            Acteurs /
                            <strong>Ajout d'un acteur</strong>
                        </h1>
                        <p class="mt-2 mb-4">Vous êtes sur la page d'ajout d'acteurs. C'est ici que vous nous donnez les informations sur l'acteur à ajouter.</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Formulaire d'ajout</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Messages divers -->
                                    <?php if (isset($doublon) && $doublon) : ?>
                                        <?php afficherAlerte('Il semble que vous ayiez déjà enregistré un acteur avec des informations très similaires', 'danger') ?>
                                    <?php endif; ?>
                                    <!-- Fin Messages divers -->

                                    <!-- Formulaire : Début -->

                                    <form action="" method="post" enctype="multipart/form-data">
                                        <?php require_once('includes/fieldsets.php') ?>
                                        <div class="mt-4">
                                            <button type="submit" name="ajouter_participant" class="btn btn-primary mr-2">Ajouter le participant</button>
                                            <a href="<?= generateUrl('participants')?>" class="btn btn-outline-primary">Annuler</a>
                                        </div>

                                    </form>
                                    <!-- Formulaire : Fin -->

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