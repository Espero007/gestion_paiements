<?php
$titre_page = "Génération des données aléatoires";
$msg_loader = "Les données sont en cours de génération et de configuration...";
require_once(__DIR__ . '/../includes/header.php');
?>

<body id="page-top">
    <script>
        setTimeout(() => window.location.href = "./traitements/gen_demo.php", 500);
    </script>
</body>