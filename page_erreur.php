<?php

session_start();

if (!isset($_SESSION['code_erreur'])) {
    // Anomalie car ce n'est pas moi qui ai dirigé vers cette page donc on va définir le type de l'erreur sur 403
    $code_erreur = 403;
} else {
    // C'est bien moi qui ai dirigé vers cette page avec un code d'erreur valide
    $code_erreur = $_SESSION['code_erreur'];
}

// Définition des messages d'erreurs pour chaque code d'erreur

if ($code_erreur == 403) {
    $titre_erreur = "Acces non autorisé";
    $intitule_erreur = "Il semble que vous n'avez pas accès à cette page que vous demandez";
} elseif ($code_erreur == 404) {
    $titre_erreur = "Page non retrouvée";
    $intitule_erreur = "Il semble que nous avons rencontré un problème...";
} elseif ($code_erreur == 500) {
    $titre_erreur = "Erreur interne du serveur";
    $intitule_erreur = "Il semble que nous avons rencontré un problème...";
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Erreur <?php echo $code_erreur; ?></title>

    <!-- Custom fonts for this template-->
    <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body id="page-top">
    <style>
        body {
            height: 100vh;
        }

        div#wrapper {
            height: 100%;
        }

        footer.mb-2 {
            margin-bottom: 0 !important;
        }
    </style>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content" class="d-flex align-items-center">

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Error Text -->
                    <div class="text-center">
                        <div class="error mx-auto" data-text="<?php echo $code_erreur; ?>"><?php echo $code_erreur; ?></div>
                        <p class="lead text-gray-800 mb-2"><?php echo $titre_erreur; ?></p>
                        <p class="text-gray-500 mb-0"><?php echo $intitule_erreur; ?></p>
                        <?php if (isset($_SESSION['previous_url'])) : ?>
                            <a href="<?php echo $_SESSION['previous_url']; ?>">Revenir à la page précédente</a>
                            <span> | </span>
                        <?php endif; ?>
                        <a href="index.php">Revenir au tableau de bord</a>
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
</body>

</html>