<?php

require_once('includes/header.php');
require_once('traitements/submit_creer_activite.php');

?>

<body id="page-top">
    <style>
        a:hover.no-decoration {
            text-decoration: none;
        }
    </style>

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
                        <h1 class="h3 mb-4 text-gray-800">Création d'une activité</h1>
                        <p class="mt-2">Vous êtes sur le point de créer une activité. Nous allons vous guider tout au long du processus.</p>
                        <hr>
                    </div>

                    <?php
                    if (isset($recuperation_type_activite) && !$recuperation_type_activite) {
                        // Le type de l'activité n'a pas encore été choisi
                    ?>
                        <p class="mt-2">Commencez par nous dire quel type d'activité vous aimeriez créer. Nous disposons de trois types qui ont chacun leurs particularités.</p>

                        <!-- Content Row -->
                        <div class="row">
                            <!-- Content Column -->
                            <div class="col-lg-10 mb-4">
                                <!-- Color System -->
                                <div class="row">

                                    <div class="col-lg-6 mb-4">
                                        <div class="card bg-light text-black shadow">
                                            <a href="creer_activite.php?id_activite=1" class="no-decoration">
                                                <div class="card-body">
                                                    1
                                                    <div class="text-black-50 small no-decoration">Ce type d'activité est défini par le montant par jour et le nombre de jour de travail. </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                    <!-- </a> -->

                                    <div class="col-lg-6 mb-4">
                                        <div class="card bg-light text-black shadow">
                                            <a href="creer_activite.php?id_activite=2" class="no-decoration">
                                                <div class="card-body">
                                                    2
                                                    <div class="text-black-50 small">Ce type d'activité est défini par le montant par jour, le nombre de jour de travail et une indemnité forfaitaire pour certains acteurs. </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 mb-4">
                                        <div class="card bg-light text-black shadow">
                                            <a href="creer_activite.php?id_activite=3" class="no-decoration">
                                                <div class="card-body">
                                                    3
                                                    <div class="text-black-50 small">Ce dernier type d'activité est défini par le nombre de tache, le montant par tache, les frais de déplacement par jour, le nombre de jours et un forfait pour certains acteurs.</div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    } elseif (isset($recuperation_type_activite) && $recuperation_type_activite) {
                        // Le type de l'activité a été récupéré et est valide
                    ?>
                        <p class="mt-2">Dîtes-nous en plus sur votre activité à présent.</p>
                        <div class="row">
                            <div class="col-12">
                                <!-- Basic Card Example -->
                                <div class="card shadow mb-4">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Formulaire d'ajout</h6>
                                    </div>
                                    <div class="card-body">

                                        <!-- Formulaire -->

                                        <form action="" method="post">

                                            <fieldset>
                                                <legend class="h6">Informations générales</legend>
                                                <hr>
                                                <div class="mb-2 row">
                                                    <label for="nom" class="col-sm-3 col-form-label">Nom</label>
                                                    <div class="col-sm-9">
                                                        <input
                                                            type="text"
                                                            name="nom"
                                                            maxlength="50"
                                                            id="nom"
                                                            class="form-control
                                                <?php if (isset($erreurs["nom"])) {
                                                    echo "is-invalid\" aria-describedby=\"nomAide";
                                                } ?>"
                                                            placeholder="Entrez le nom de l'activité"
                                                            <?php if (isset($erreurs)) {
                                                                echo "value = \"" . $_POST["nom"] . "\"";
                                                            } ?>>

                                                        <?php if (isset($erreurs["nom"])) {
                                                        ?>
                                                            <div id="nomAide" class="form-text"><?php echo $erreurs["nom"][0] ?></div>
                                                        <?php } ?>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary mt-5 mb-4" name="creer">Créer l'activité</button>
                                            </fieldset>



                                        </form>

                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    }

                    ?>


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