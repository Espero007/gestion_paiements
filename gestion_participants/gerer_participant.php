<?php
$titre_page = "Gestion du participant";
require_once(__DIR__.'/../includes/header.php');
require_once('includes/gerer_participant.php');
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
                    <h1 class="h4 mb-4 text-gray-800">Participants / <strong>Gestion du participant</strong></h1>
                    <p class="mt-2">Gérez ici votre participant</p>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Fiche informative</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['modification_ok'])) : ?>
                                <div class="alert alert-success mt-2">Les informations du participant ont été modifiées avec succès !</div>
                                <?php unset($_SESSION['modification_ok']) ?>
                            <?php endif; ?>

                            <?php foreach ($infos as $info => $valeur) : ?>
                                <p class="mb-3">
                                    <?php if (str_contains($info, 'compte_')) : ?>
                                        <span class="font-weight-bold">Banque (RIB) : </span>
                                        <span><?= htmlspecialchars($valeur[0]) . ' <i>(' . htmlspecialchars($valeur[1]) . '</i>)' ?></span>
                                    <?php else: ?>
                                        <span class="font-weight-bold"><?= $valeur ?> : </span>
                                        <span><?= $participant[$info] ?></span>
                                    <?php endif; ?>
                                </p>
                            <?php endforeach; ?>

                            <hr>
                            <p><strong>Que voulez-vous faire ?</strong></p>

                            <!-- Boutons d'action -->
                            <div>
                                <a href="modifier_informations.php?id_participant=<?= $participant['id_participant'] ?>" class="btn btn-primary mr-2">Modifier</a>
                                <a href="lier_participant_activite.php?id_participant=<?= $participant['id_participant'] ?>" class="btn btn-outline-primary mr-2">Associer à une activité</a>

                                <!-- Autres options -->
                                <div class="btn-group dropup">
                                    <button type="button" class="dropdown-toggle btn btn-outline-primary" data-bs-toggle="dropdown" aria-expanded="false">Autres </button>

                                    <ul class="dropdown-menu shadow">
                                        <?php if (!$quota_comptes_bancaires_atteint) : ?>
                                            <li><a href="ajouter_comptes.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item">Ajouter des comptes bancaires</a></li>
                                        <?php endif; ?>
                                        <li>
                                            <hr class="dropwdown-divider">
                                        </li>
                                        <li>
                                            <a href="#" class="dropdown-item text-danger custom-dropdown-item">Supprimer</a>
                                        </li>
                                    </ul>
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
    <?php require_once(__DIR__.'/../includes/logoutModal.php') ?>
    <?php require_once(__DIR__.'/../includes/scripts.php') ?>

    <!-- Page level custom scripts -->
    <script src="/assets/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>