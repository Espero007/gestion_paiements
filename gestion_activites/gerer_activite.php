<?php
$section = 'Activités';
$titre = "Gestion de l'activité";
require_once(__DIR__ . '/../includes/header.php');
require_once('traitements/gerer_activite.php');

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
                    <h1 class="h4 mb-4 text-gray-800">Activités / <strong>Gestion de l'activité</strong></h1>
                    <p class="mt-2">Gérez ici votre activité</p>
                    
                    <!-- Afficher un message de succèes si l'activité a été bien modifié -->

                   <?php $success = isset($_GET['success']);
                       $id_activite = $_GET['id']  ?>

                    <?php if ($success==='1') : ?>
                        <div class="alert alert-success alert-dismissible text-center">
                            Vos modifications  ont été enregistrée avec succès ! Pensez à y <a href="/gestion_participants/lier_participant_activite.php?id_activite=<?= $id_activite ?>">associer des participants</a>.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($success==='2') : ?>
                         <div class="alert alert-success alert-dismissible text-center">
                            Votre activité a été enregistrée avec succès ! Pensez à y <a href="/gestion_participants/lier_participant_activite.php?id_activite=<?= $id_activite ?>">associer des participants</a>.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Fiche informative</h3>
                        </div>
                        <div class="card-body">

                            <!-- Nom -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Nom : </span>
                                <span><?= $activite['nom'] ?></span>
                            </p>

                            <!-- Description -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Description : </span>
                                <span><?= $activite['description'] ?></span>
                            </p>

                            <!-- Période -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Période : </span>
                                <span>Du <?= $activite['date_debut'] ?> au <?= $activite['date_fin'] ?></span>
                            </p>

                            <!-- Centre -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Centre : </span>
                                <span><?= $activite['centre'] ?></span>
                            </p>

                            <!-- Premier responsable et titre premier responsable -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Premier responsable : </span>
                                <span><?= $activite['premier_responsable'] ?></span>
                                <?php if (!empty($activite['titre_responsable'])) : ?>
                                    <span>(<?= $activite['titre_responsable'] ?>)</span>
                                <?php endif; ?>
                            </p>

                            <!-- Organisateur et titre organisateur -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Organisateur : </span>
                                <span><?= $activite['organisateur'] ?></span>
                                <?php if (!empty($activite['titre_organisateur'])) : ?>
                                    <span>(<?= $activite['titre_organisateur'] ?>)</span>
                                <?php endif; ?>
                            </p>

                            <!-- Financier & titre financier -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Financier : </span>
                                <span><?= $activite['financier'] ?></span>
                                <?php if (!empty($activite['titre_financier'])) : ?>
                                    <span>(<?= $activite['titre_financier'] ?>)</span>
                                <?php endif; ?>
                            </p>

                            <!-- Taux journalier -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Taux journalier : </span>
                                <span><?= $activite['taux_journalier'] ?> FCFA</span>
                            </p>

                            <?php if ($activite['type_activite'] == 2 || $activite['type_activite'] == 3) : ?>
                                <!-- Frais de déplacement journalier -->
                                <p class="mb-3">
                                    <span class="font-weight-bold">Frais de déplacement journalier : </span>
                                    <span><?= $activite['frais_deplacement_journalier'] ?></span>
                                </p>

                                <!-- Taux par tâche -->
                                <p class="mb-3">
                                    <span class="font-weight-bold">Taux par tâche : </span>
                                    <span><?= $activite['taux_taches'] ?></span>
                                </p>
                            <?php endif; ?>

                            <hr>
                            <p><strong>Que voulez-vous faire ?</strong></p>

                            <!-- Boutons d'action -->
                            <div class="mt-4">
                                <a href="modifier_infos.php?id=<?= $activite['id'] ?>" class="btn btn-primary mr-2">Modifier</a>
                                <a href="/gestion_participants/lier_participant_activite.php?id_activite=<?= $activite['id'] ?>" class="btn btn-outline-primary mr-2">Associer des participants</a>

                                <!-- Autres options -->

                                <div class="btn-group dropup">
                                    <button type="button" class="dropdown-toggle btn btn-outline-primary" data-bs-toggle="dropdown" aria-expanded="false">Autres </button>

                                    <ul class="dropdown-menu shadow">
                                        <!-- <li>
                                            <hr class="dropwdown-divider">
                                        </li> -->
                                        <li>
                                            <a href="#" class="dropdown-item text-danger fs-6">Supprimer</a>
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

    <!-- Page level custom scripts -->
    <script src="/assets/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>