<?php
$section = 'Participants';
$titre_page = "Gestion du participant";
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
                    <h1 class="h4 mb-4 text-gray-800">Participants / <strong>Gestion du participant</strong></h1>
                    <p class="mt-2">Gérez ici votre participant</p>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Fiche informative</h3>
                        </div>
                        <div class="card-body">
                            <?php if (isset($_SESSION['modification_ok'])) : ?>
                                <?php afficherAlerte('Les informations du participant ont été modifiées avec succès !', 'success') ?>
                                <?php unset($_SESSION['modification_ok']) ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['participant_ajoute'])) : ?>
                                <?php afficherAlerte('participant_ajoute', 'success', true) ?>
                            <?php endif; ?>

                            <!-- Informations générales -->

                            <?php foreach ($infos as $info => $valeur) : ?>
                                <p class="mb-3">
                                    <span class="font-weight-bold"><?= $valeur ?> : </span>
                                    <span><?= htmlspecialchars($participant[$info]) ?></span>
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

                                <ul class="dropdown-menu shadow">
                                    <?php if (!$quota_comptes_bancaires_atteint) : ?>
                                        <li><a href="ajouter_comptes.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item">Ajouter des comptes bancaires</a></li>
                                    <?php endif; ?>
                                    <li>
                                        <a href="modifier_informations.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item">Modifier les informations</a>
                                    </li>

                                    <li>
                                        <a href="lier_participant_activite.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item"></i>Associer à une activité</a>
                                    </li>
                                    <li>
                                        <hr class="dropwdown-divider">
                                    </li>
                                    <li>
                                        <a href="#" class="dropdown-item text-danger custom-dropdown-item del-btn" id='<?= $participant['id_participant'] ?>' data-toggle="modal" data-target="#deletionModal">Supprimer</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="card shadow mb-4"> -->
                    <!-- <div class="card-header"> -->
                    <!-- <h6 class="text-pirmary font-weight-bold">Activités associées</h6> -->
                    <!-- </div> -->
                    <!-- <div class="card-body">
                            Messages divers s'il y en a

                            <?php if (count($activites_associees) == 0) : ?>
                                <div class="text-center">
                                    <p>Vous n'avez encore associé <strong><?= htmlspecialchars($participant['nom'] . ' ' . $participant['prenoms']) ?></strong> à aucune activité semble t'il. Faîtes le dès maintenant.</p>
                                    <a href="/gestion_participants/lier_participant_activite.php?id_participant=<?= $participant['id_participant'] ?>" class="btn btn-outline-primary">Associer à une activité</a>
                                </div>

                            <?php else: ?>

                            <?php endif; ?>
                        </div> -->
                    <!-- </div> -->
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

    <!-- Suppression Modal -->
    <div class="modal fade" id="deletionModal" tabindex="-1" role="dialog" aria-labelledby="deletionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Êtes-vous sûr(e) de vouloir continuer ?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">En appuyant sur "Supprimer" vous ne pourrez plus faire marche arrière.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                    <a class="btn btn-danger" href="#" id='deletionModalBtn'>Supprimer</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal-->

    <?php require_once(__DIR__ . '/../includes/logoutModal.php') ?>
    <?php require_once(__DIR__ . '/../includes/scripts.php') ?>

    <script>
        const deleteBtns = document.querySelectorAll('.del-btn'); // boutons de suppression des participants
        const deletionModalBtn = document.getElementById('deletionModalBtn');

        deleteBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const id_participant = btn.id;
                deletionModalBtn.href = '/gestion_participants/supprimer_participant.php?id=' + id_participant;
            })
        })
    </script>
</body>

</html>