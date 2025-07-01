<?php
$section = 'Participants';
$titre_page = "Liste des participants";
require_once(__DIR__ . '/../includes/header.php');

$stmt = 'SELECT id_participant, nom, prenoms, matricule_ifu, date_naissance, lieu_naissance FROM participants WHERE id_user=' . $_SESSION['user_id'] . ' ORDER BY id_participant';
$resultat = $bdd->query($stmt);

if (!$resultat) {
    redirigerVersPageErreur(500, obtenirURLcourant());
} else {
    // Les données sont récupérées
    while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) {
        $participants[] = $ligne;
    }
}
$resultat->closeCursor();
// $participants = [];
?>

<!-- Custom styles for this page -->
<link href="/assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

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

                    <?php if (isset($participants)) : ?>
                        <!-- Nous avons des participants -->

                        <!-- Page Heading -->
                        <h1 class="h4 mb-4 text-gray-800">Participants / <strong>Vos participants</strong></h1>
                        <p class="mt-2">Ici vous avez la liste de tous les participants que vous avez déjà ajouter. A partir des options disponibles vous pouvez modifier leurs informations, les supprimer, les associer à des activités, et bien plus.</p>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Liste des participants</h6>
                            </div>
                            <div class="card-body">
                                <!-- Messages de succès -->

                                <!-- Ajout de comptes -->
                                <?php if (isset($_SESSION['comptes_ajoutes'])) : ?>
                                    <div class="alert alert-success mt-2 text-center alert-dismissible">
                                        Le(s) compte(s) bancaire(s) a(ont) été ajouté(s) avec succès !
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                    </div>
                                    <?php unset($_SESSION['comptes_ajoutes']); ?>
                                <?php endif; ?>

                                <!-- Liaison participant-activité réussie -->
                                <?php if (isset($_SESSION['liaison_reussie'])) : ?>
                                    <div class="alert alert-success mt-2 text-cente alert-dismissibler">
                                        La liaison a été faite avec succès !
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                    </div>
                                    <?php unset($_SESSION['liaison_reussie']); ?>
                                <?php endif; ?>

                                <!-- Liaison participant-activité non autorisée -->
                                <?php if (isset($_SESSION['liaison_non_autorisee'])) : ?>
                                    <div class="alert alert-danger mt-2 text-center alert-dismissible">
                                        Ce participant a déjà été lié à cette activité !
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                    </div>
                                    <?php unset($_SESSION['liaison_non_autorisee']); ?>
                                <?php endif; ?>

                                <!-- Participant supprimé avec succès -->
                                <?php if (isset($_SESSION['suppression_ok'])) : ?>
                                    <div class="alert alert-success mt-2 text-center alert-dismissible">
                                        Le participant a été supprimé avec succès !
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                    </div>
                                    <?php unset($_SESSION['suppression_ok']); ?>
                                <?php endif; ?>

                                <form action="">
                                    <div class="">
                                        <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Choix</th>
                                                    <th>Nom</th>
                                                    <th>Prénom(s)</th>
                                                    <th>Matricule/IFU</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Choix</th>
                                                    <th>Nom</th>
                                                    <th>Prénom(s)</th>
                                                    <th>Matricule/IFU</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                                <?php foreach ($participants as $participant) : ?>

                                                    <tr>
                                                        <td><input type="checkbox" name="bref" id="bref"></th>
                                                        <td><?= htmlspecialchars($participant['nom']) ?></td>
                                                        <td><?= htmlspecialchars($participant['prenoms']) ?></td>
                                                        <td><?= htmlspecialchars($participant['matricule_ifu']) ?></td>
                                                        <!-- <td>
                                                            Gérer
                                                            <a href="/gestion_participants/modifier_informations.php?id_participant=<?= $participant['id_participant'] ?>"><button class="btn btn-primary">Modifier</button></a>
                                                            <button class="btn btn-danger">Supprimer</button>
                                                        </td> -->
                                                        <td>
                                                            <!-- <a href="/gestion_participants/">Associer à une activité</a> -->
                                                            <div class="btn-group">
                                                                <a href="/gestion_participants/gerer_participant.php?id=<?= $participant['id_participant'] ?>" class="btn btn-primary">Gérer</a><br>

                                                                <button type="button" class="btn btn-primary btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a href="gerer_participant.php?id=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item">Voir</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="modifier_informations.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item">Modifier les informations</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="ajouter_comptes.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item">Ajouter des comptes bancaires</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="lier_participant_activite.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item custom-dropdown-item"></i>Associer à une activité</a>
                                                                    </li>
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li>
                                                                        <a href="#" class="dropdown-item text-danger custom-dropdown-item del-btn" id='<?= $participant['id_participant'] ?>' data-toggle="modal" data-target="#deletionModal"></i>Supprimer</a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <h3 class="font-weight-bold">Aucun participant retrouvé !</h3>
                            <p class="mt-3 text-center">Il semble que vous n'ayiez aucun participant déjà ajouté. Pourquoi ne pas remédier à celà et en ajouter dès maintenant ?</p>
                            <a href="ajouter_participant.php" class="btn btn-outline-primary">Ajouter un participant</a>
                            <div class="mt-5 mb-5">
                                <img src="/assets/illustrations/no-results-1.png" alt="no results" class="img-fluid" width="500">
                            </div>
                        </div>


                    <?php endif; ?>
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

    <!-- Page level plugins -->
    <script src="/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="/assets/js/demo/datatables-demo.js"></script>
    <script src="/assets/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>

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