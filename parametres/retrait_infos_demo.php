<?php
$titre_page = "Retrait des informations de démo";
$msg_loader = "Patientez le temps que nous retirions les données...";
require_once(__DIR__ . '/../includes/header.php');
?>

<body id="page-top">
    <script>
        setTimeout(() => window.location.href = "./traitements/ret_demo.php", 500);
    </script>
</body>
