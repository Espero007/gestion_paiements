<?php
$section = 'Participants';
$titre_page = "Gestion de l'acteur";
require_once(__DIR__ . '/../includes/header.php');

require_once('includes/gerer_participant.php');
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
                    <h1 class="h4 mb-4 text-gray-800">Acteurs / <strong>Gestion de l'acteur</strong></h1>
                    <p class="mt-2">Gérez ici votre acteur</p>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Fiche informative</h3>
                        </div>
                        <div class="card-body">
                            <!-- Messages divers -->
                            <?php if (isset($_SESSION['liaison_reussie'])) : ?>
                                <?php afficherAlerte('liaison_reussie', 'success', true); ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['modification_ok'])) : ?>
                                <?php afficherAlerte('Les informations du participant ont été modifiées avec succès !', 'success') ?>
                                <?php unset($_SESSION['modification_ok']) ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['participant_ajoute'])) : ?>
                                <?php afficherAlerte('participant_ajoute', 'success', true) ?>
                            <?php endif; ?>

                            <!-- Ajout de comptes -->
                            <?php if (isset($_SESSION['comptes_ajoutes'])) : ?>
                                <?php afficherAlerte('comptes_ajoutes', 'success', true) ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['comptes_supprimes'])) : ?>
                                <?php afficherAlerte('comptes_supprimes', 'success', true) ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['modification_reussie'])) : ?>
                                <?php afficherAlerte('modification_reussie', 'success', true) ?>
                            <?php endif; ?>

                            <!-- Fin Messages divers -->

                            <!-- Informations générales -->

                            <?php foreach ($infos as $info => $valeur) : ?>
                                <p class="mb-3">
                                    <span class="font-weight-bold"><?= $valeur ?> : </span>
                                    <span><?= $info != 'date_naissance' ? htmlspecialchars($participant[$info]) : htmlspecialchars(date('d/m/Y', strtotime($participant[$info]))) ?></span>
                                </p>
                            <?php endforeach; ?>

                            <!-- Informations bancaires -->

                            <p class="mb-0">
                                <span class='font-weight-bold'> Banque<?= count($comptes) > 1 ? 's' : '' ?> (RIB<?= count($comptes) > 1 ? 's' : '' ?>) :
                                </span>
                                <?= $comptes_str ?>
                                <br>
                                <small><strong>Note</strong> : Cliquez sur un compte bancaire si vous souhaitez visualiser la copie PDF de son RIB</small>
                            </p>
                        </div>
                        <hr class="m-0">
                        <div class="card-body">
                            <!-- Boutons d'action -->

                            <a href="modifier_informations.php?id_participant=<?= $participant['id_participant'] ?>" class="btn btn-primary mr-2">Modifier les informations</a>

                            <!-- Autres options -->
                            <div class="btn-group dropup">
                                <button type="button" class="dropdown-toggle btn btn-outline-primary" data-bs-toggle="dropdown" aria-expanded="false">Autres options</button>

                                <?php require_once('options.php') ?>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-pirmary font-weight-bold">Activités associées</h6>
                        </div>
                        <div class="card-body">
                            <?php if (count($activites_associees) == 0) : ?>
                                <div class="text-center">
                                    <p>Vous n'avez encore associé <strong><?= htmlspecialchars($participant['nom'] . ' ' . $participant['prenoms']) ?></strong> à aucune activité semble t'il. Faîtes le dès maintenant.</p>
                                    <a href="/gestion_participants/lier_participant_activite.php?id_participant=<?= $participant['id_participant'] ?>" class="btn btn-outline-primary">Associer à une activité</a>
                                </div>
                            <?php else: ?>
                                <?php afficherSousFormeTableau($informations, '', 'table-bordered text-center', false, true) ?>
                            <?php endif; ?>
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
    <!-- Autres modals -->
    <?php require_once(__DIR__ . '/../includes/modals.php') ?>
    <?php require_once(__DIR__ . '/../includes/scripts.php') ?>
</body>

</html>