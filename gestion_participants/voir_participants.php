<?php
$titre = "Liste des participants";
require_once('includes/header.php');

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

                    <h1 class="h4 mb-4 text-gray-800">Participants / <strong>Vos participants</strong></h1>
                    <p class="mt-2">Ici vous avez la liste de tous les participants que vous avez déjà ajouter. A partir des options disponibles vous pouvez modifier leurs informations, en supprimer, les associer à des activités, etc...</p>
                    <hr>

                    <?php if (count($participants) == 0) : ?>
                        <p>Il semble que vous n'avez encore aucun participant d'enregistré. <a href="/gestion_participants/ajouter_participant.php">Ajoutez-en ici</a></p>
                    <?php else: ?>
                        <?php // Nous avons des participants déjà enregistrés 
                        ?>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Liste des participants</h6>
                            </div>
                            <div class="card-body">
                                <?php if(isset($_SESSION['comptes_ajoutes'])) : ?>
                                    <div class="alert alert-success mt-2">Le(s) compte(s) bancaire(s) a(ont) été ajouté(s) avec succès !</div>
                                    <?php unset($_SESSION['comptes_ajoutes']); ?>
                                <?php endif; ?>
                               
                                <form action="">
                                    <div class="">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
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
                                                    <!-- <th>Associer à une activité</th> -->
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                                <?php foreach ($participants as $participant) : ?>

                                                    <tr>
                                                        <td><input type="checkbox" name="bref" id="bref"></th>
                                                        <td><?= $participant['nom'] ?></td>
                                                        <td><?= $participant['prenoms'] ?></td>
                                                        <td><?= $participant['matricule_ifu'] ?></td>
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
                                                                        <a href="gerer_participant.php?id=<?= $participant['id_participant'] ?>" class="dropdown-item d-flex align-items-center fs-6"><i class="bi bi-box-arrow-up-right mr-2"></i>Voir</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="modifier_infos.php?id=<?= $participant['id_participant'] ?>" class="dropdown-item d-flex align-items-center fs-6"><i class="bi bi-box-arrow-up-right mr-2"></i>Modifier</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="ajouter_comptes.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item d-flex align-items-center fs-6"><i class="bi bi-box-arrow-up-right mr-2"></i>Ajouter des comptes bancaires</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="lier_participant_activite.php?id_participant=<?= $participant['id_participant'] ?>" class="dropdown-item d-flex align-items-center fs-6"><i class="bi bi-link mr-2"></i>Associer à une activité</a>
                                                                    </li>
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li>
                                                                        <a href="#" class="dropdown-item d-flex align-items-center text-danger fs-6"><i class="bi bi-trash mr-2"></i>Supprimer</a>
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
                    <?php endif; ?>
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

    <!-- Page level plugins -->
    <script src="/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="/assets/js/demo/datatables-demo.js"></script>
    <script src="/assets/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>