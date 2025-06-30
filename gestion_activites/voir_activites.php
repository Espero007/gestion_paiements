<?php
$section = 'Activités';
$titre_page = "Liste des activités";
require_once(__DIR__.'/../includes/header.php');

$stmt = 'SELECT id, nom, description, centre FROM activites ORDER BY id DESC';
$resultat = $bdd->query($stmt);

if (!$resultat) {
    redirigerVersPageErreur(500, obtenirURLcourant());
} else {

    if ($resultat->rowCount() != 0) {
        // On a des activités

        // Les données sont récupérées
        while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) {
            $activites[] = $ligne;
        }

        foreach ($activites as $index => $activite) {

            # Traitement de la description pour qu'elle n'excède pas 18 mots
            // $nbr_mots = 18;
            // $description = explode(' ', $activite['description']); // retourne dans un tableau les mots de la description

            // if (count($description) > $nbr_mots) {
            //     $description = array_slice($description, 0, $nbr_mots); // retourne les $nbr_mots premiers éléments du tableau
            //     $description[count($description)-1] = '...';
            // }

            // $description = implode(' ', $description);
            // $activites[$index]['description'] = $description;

            # Traitement des dates de début et de fin pour obtenir la période de l'activité

            // $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Africa/Lagos', IntlDateFormatter::GREGORIAN);
            // $activites[$index]['periode'] = "Du " . $fmt->format(new DateTime($activite['date_debut'])) . " au " . $fmt->format(new DateTime($activite['date_fin']));
        }
    }
}
$resultat->closeCursor();
?>

<!-- Custom styles for this page -->
<link href="/assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">

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
                    <?php if (isset($erreur_recuperation)) : ?>
                        <div class="alert alert-danger">La récupération des données a rencontré un problème.</div>
                    <?php endif; ?>


                    <?php if (isset($activites)) : ?>
                        <!-- Page Heading -->
                        <h1 class="h4 mb-4 text-gray-800">Activités / <strong>Vos activités</strong></h1>
                        <p class="mt-2">Ici vous avez accès à toutes les activités que vous avez créées. (Cliquez <a href="creer_activite.php">ici</a> pour en créer une autre)</p>
                        <hr>
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Liste des activités</h6>
                            </div>
                            <div class="card-body">
                                <form action="">
                                    <div class="">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Choix</th>
                                                    <th>Titre</th>
                                                    <th>Description</th>
                                                    <th>Centre</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Choix</th>
                                                    <th>Titre</th>
                                                    <th>Description</th>
                                                    <th>Centre</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                                <?php foreach ($activites as $activite) : ?>
                                                    <tr>
                                                        <td><input type="checkbox" name="bref" id="bref"></th>
                                                        <td><?= htmlspecialchars($activite['nom']) ?></td>
                                                        <td><?= couperTexte(htmlspecialchars($activite['description']), 15, 200) ?></td>
                                                        <td><?= htmlspecialchars($activite['centre']) ?></td>
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="gerer_activite.php?id=<?= $activite['id'] ?>" class="btn btn-primary">Gérer</a>

                                                                <button type="button" class="btn btn-primary btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                                <ul class="dropdown-menu">
                                                                    <li>
                                                                        <a href="gerer_activite.php?id=<?= $activite['id'] ?>" class="dropdown-item custom-dropdown-item">Voir</a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="modifier_infos.php?id=<?= $activite['id'] ?>" class="dropdown-item custom-dropdown-item">Modifier les informations</a>
                                                                    </li>
                                                                    <!-- <li>
                                                                        <a href="ajouter_comptes.php?id_participant=<?= $activite['id'] ?>" class="dropdown-item custom-dropdown-item">Ajouter des comptes bancaires</a>
                                                                    </li> -->
                                                                    <li>
                                                                        <a href="/gestion_participants/lier_participant_activite.php?id_activite=<?= $activite['id'] ?>" class="dropdown-item custom-dropdown-item"></i>Associer des participants</a>
                                                                    </li>
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li>
                                                                        <a href="#" class="dropdown-item text-danger custom-dropdown-item"></i>Supprimer</a>
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
                    <?php else : ?>
                        <div class="text-center">
                            <h3 class="font-weight-bold">Aucune activité retrouvée !</h1>
                                <p class="mt-3 text-center">Il semble que vous n'ayiez aucune activité déjà créée. Pourquoi ne pas corriger le tir et en créer dès maintenant ?</p>
                                <a href="creer_activite.php" class="btn btn-outline-primary">Créer une activité</a>
                                <div class="mt-5 mb-5">
                                    <img src="/assets/illustrations/no-results.png" alt="no results" class="img-fluid" width="400">
                                </div>
                        </div>
                    <?php endif; ?>


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

    <!-- Page level plugins -->
    <script src="/assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="/assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="/assets/js/demo/datatables-demo.js"></script>
    <script src="/assets/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>