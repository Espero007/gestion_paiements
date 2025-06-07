<?php
$titre = 'Liaison Participant - Activité';
require_once('includes/header.php');
require_once(__DIR__ . '/includes/traitements_lier_participant_activite.php');
?>
<link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">

<body>
    <div class="container-md pt-4">
        <h5>Page de liaison d'un participant à une activité</h5>
        <p>En cours de développement...</p>

        <?php foreach ($activites as $activite) : ?>
            <hr>
            <div class="mb-4">
                <p> <strong>Nom : </strong><?= $activite['nom'] ?></p>
                <p> <strong>Description : </strong><?= $activite['description'] ?></p>
                <p> <strong>Date_debut : </strong><?= $activite['date_debut'] ?></p>
                <p> <strong>Date_fin : </strong><?= $activite['date_fin'] ?></p>
                <p> <strong>Centre : </strong><?= $activite['centre'] ?></p>

                
                <button class="btn btn-primary">Choisir</button>
            </div>
        <?php endforeach; ?>
    </div>


    <!-- 

    1- Récupérer la liste des activités créées par cet utilisateur
    2 - permettre le choix d'une activité
    3
    
    -->

</body>

</html>