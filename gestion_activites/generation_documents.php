<?php
$section = 'Activités';
$titre_page = "Génération de documents";
require_once(__DIR__ . '/../includes/header.php');
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
                    <h1 class="h4 mb-4 text-gray-800">Activités / <strong>Génération de documents</strong></h1>
                    <p class="mt-2">Générez les documents associés à votre activité.</p>

                    <!-- Documents à générer -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Documents à générer</h6>
                        </div>
                        <div class="card-body">

                            <p class=""><strong>Note</strong> : Pour une activité, 06 documents peuvent être générés et téléchargés en PDF : la note de service, l'attestation collective de travail, l’état de paiement, les ordres de virements, la synthèse des ordres de virements et la liste des RIB des participants. Choisissez en dessous les documents que vous voulez générer et télécharger parmi les 06.</p>

                            <div class="divider text-start">
                                <div class="divider-text"><strong>Liste des documents</strong></div>
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