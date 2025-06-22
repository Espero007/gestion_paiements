<?php
// require_once(__DIR__ . "/../../includes/bdd.php");
require_once(__DIR__.'/../includes/header.php');

?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once(__DIR__.'/../includes/sidebar.php') ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php require_once(__DIR__.'/../includes/topbar.php') ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div>
                        <h1 class="h3 mb-4 text-gray-800">Création d'une activité</h1>
                        <p class="mt-2">Vous êtes sur le point de créer une activité. Nous allons vous guider tout au long du processus.</p>
                        <p class="mt-2">Commencez par nous dire quel type d'activité vous aimeriez créer. Nous disposons de trois types qui ont chacune leurs particularités.</p>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Content Column -->
                        <div class="col-lg-10 mb-4">
                            <!-- Color System -->
                            <div class="row">

                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-light text-black shadow">
                                        <div class="card-body">
                                            <a href="creation_activite/formcreateactivity.php">
                                                1
                                            </a>
                                            <div class="text-black-50 small">Ce type d'activité est défini par le montant par jour et le nombre de jour de travail. </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- </a> -->

                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-light text-black shadow">
                                        <div class="card-body">
                                            <a href="creation_activite/formcreateactivity2.php">2</a>
                                            <div class="text-black-50 small">Ce type d'activité est défini par le montant par jour, le nombre de jour de travail et une indemnité forfaitaire pour certains acteurs. </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-4">
                                    <div class="card bg-light text-black shadow">
                                        <div class="card-body">
                                            <a href="creation_activite/formcreateactivity3.php">3</a>
                                            <div class="text-black-50 small">Ce dernier type d'activité est défini par le nombre de tache, le montant par tache, les frais de déplacement par jour, le nombre de jours et un forfait pour certains acteurs.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php require_once(__DIR__.'/../includes/footer.php') ?>
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
    <?php require_once(__DIR__.'../includes/logoutModal.php') ?>
    <?php require_once(__DIR__.'../includes/scripts.php') ?>
</body>

</html>