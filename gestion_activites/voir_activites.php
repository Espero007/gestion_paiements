<?php
$titre = "Liste des activités";
require_once('includes/header.php');

$stmt = 'SELECT id, nom, description FROM activites ORDER BY id DESC LIMIT ' . NBR_ACTIVITES_A_AFFICHER;
$resultat = $bdd->query($stmt);

if (!$resultat) {
    redirigerVersPageErreur(500, obtenirURLcourant());
} else {
    // Les données sont récupérées
    while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) {
        $activites[] = $ligne;
    }
}
$resultat->closeCursor();
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

                    <?php
                    if (isset($erreur_recuperation)) {
                        echo "<div class=\"alert alert-danger\">La récupération des données a rencontré un problème.</div>";
                    }
                    ?>

                    <!-- Page Heading -->
                    <div>
                        <h1 class="h3 mb-4 text-gray-800">Vos activités</h1>
                        <p class="mt-2">Ici vous avez accès à toutes les activités que vous avez créé pour en avoir une vue globale. A partir des options disponibles vous pouvez les modifier, y ajouter des participants, en retirer des participants, les supprimer, etc...</p>
                        <hr>

                    </div>

                    <?php

                    /** Bon je vais définir le mécanisme d'affichage des activités. Il faudra penser à un système de voir plus plus tard mais pour l'instant on se limite à trois activités
                     * 
                     * 1- Je détermine le nombre de rangées à faire apparaître. On affiche deux activités par ranger donc si le nombre d'activités est un multiple de 2, le nombre de rangées à afficher correspond tout simplement au nombre d'activités divisé par 2. Si ce nombre n'est pas un multiple, c'est qu'il y a un surplus donc ajoute dans ce cas le nombre de rangées initial + 1
                     * 
                     * 2- A présent il faut qu'on soit capable d'afficher uniquement deux activités par rangée. A cet effet on peut donc essayer une variable nombre d'activités affiché. QUand on affiche une activté on l'incrémente. S'il arrive à deux on saute le for actuel et on passe au suivant et on le réinitialise
                     * 
                     */

                    if (isset($activites)) {
                        $nbr_activites = count($activites);
                        $nbr_rows = ($nbr_activites % 2 == 0) ? $nbr_activites / 2 : $nbr_activites / 2 + 1;
                        $nbr_activites_affichees = 0;

                        $activites_temp = $activites; // activites_temp sera détruit donc il me permet de conserver les activités pour des usages ultérieurs
                        
                        for ($i = 1; $i <= $nbr_rows; $i++) { // Affichage de chaque rangée
                    ?>
                            <div class="row">
                                <?php
                                while (count($activites_temp) != 0) {
                                    // Il y a encore des activités à afficher

                                    $activite_courante = array_shift($activites_temp); // on prend la première activité dans le tableau. Je précise que les activités sont classées dans le table dans l'ordre décroissant donc du plus récemment enregistré au plus anciennement enregistré
                                ?>
                                    <div class="col-lg-6">
                                        <!-- Dropdown Card Example -->
                                        <div class="card shadow mb-4">
                                            <!-- Card Header - Dropdown -->
                                            <div
                                                class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                                <h6 class="m-0 font-weight-bold text-primary"><?php echo $activite_courante['nom'] ?></h6>
                                                <div class="dropdown no-arrow">
                                                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                                        aria-labelledby="dropdownMenuLink">
                                                        <div class="dropdown-header">Actions</div>
                                                        <a class="dropdown-item" href="#">Voir</a>
                                                        <a class="dropdown-item" href="/gestion_activites/modifier_infos.php?id_activite=<?= $activite_courante['id']?>">Modifier</a>
                                                        <a class="dropdown-item" href="#">Associer des participants</a>
                                                        <!-- <a class="dropdown-item" href="#">Another action</a> -->
                                                        <div class="dropdown-divider"></div>
                                                        <?php
                                                        // Il me faut l'id de l'activité
                                                        $url = 'http://localhost:3000/supprimer_activite.php?id_activite=' . $activite_courante['id'];
                                                        ?>

                                                        <a class="dropdown-item" href="<?php echo $url; ?>">Supprimer</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Card Body -->
                                            <div class="card-body">
                                                <?php echo $activite_courante['description'] ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                    $nbr_activites_affichees++; // Le nombre d'activités augmente
                                    if ($nbr_activites_affichees == 2) {
                                        // On quitte la boucle while
                                        break;
                                    } // Autrement on continue la boucle while    
                                }
                                ?>
                            </div>
                        <?php
                        $nbr_activites_affichees == 0;
                        }
                    } else {
                        // Il n'y a pas d'activités
                        ?>
                        <p class="mt-4">Il semble que vous n'ayiez aucune activité déjà créée. Pourquoi ne pas corriger le tir et en créer dès maintenant ?</p>
                        <a href="creer_activite.php" class="mt-4"><button class="btn btn-primary">Créer une activité</button></a>
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