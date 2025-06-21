<?php

$titre = "Ajout d'un participant";
require_once('includes/header.php');
require_once(__DIR__ . '/includes/traitements_ajout_participant.php');
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
                            Ajout d'un participant
                        </h1>
                        <p class="mt-2">Vous êtes sur la page d'ajout de participants. C'est ici que vous nous donnez les informations sur les différents participants que vous associerez à vos activités.</p>
                        <hr>
                        <p class="mt-2">Dîtes-nous en plus sur vos participants alors...</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Formulaire d'ajout</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($message_succes)) : ?>
                                        <div class="alert alert-success mt-2">Le participant a été enregistré avec succès ! <a href="./ajouter_comptes.php?id_participant=<?php echo $id_participant; ?>">Cliquez ici</a> si vous souhaitez lui ajouter des comptes bancaires.</div>
                                    <?php endif; ?>

                                    <!-- Formulaire : Début -->

                                    <form action="" method="post" enctype="multipart/form-data">
                                        <?php require_once(__DIR__ . '/includes/fieldsets.php') ?>
                                        <button type="submit" name="ajouter_participant" class="btn btn-primary mt-4">Ajouter le participant</button>
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