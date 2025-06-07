<?php
$titre = "Modification des informations";
require_once('includes/header.php');
require_once('includes/traitements_modifier_infos.php');
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
                            Modification des informations </strong>
                        </h1>
                        <p class="mt-2">Vous avez fait une erreur lors de l'enregistrement ? Des paramètres de la fiche du participant ont changé ? Ici vous pouvez rectifier la tir et corriger les informations de <strong><?= $infos_participant['nom'] . ' ' . $infos_participant['prenoms'] ?></strong></p>
                        <hr>
                        <p class="mt-2">Faîtes vos modifications...</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Formulaire de modifications</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($message_succes)) : ?>
                                        <div class="alert alert-success mt-2">Les informations du participant ont été modifiées avec succès !</div>
                                    <?php endif; ?>

                                    <!-- Formulaire : Début -->
                                     <form action="" method="post" enctype="multipart/form-data">
                                        <!-- Fieldsets -->
                                         <?php require_once('includes/fieldsets.php') ?>
                                         <button type="submit" name="modifier_infos" class="btn btn-primary mt-4">Enregistrer les modifications</button>

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