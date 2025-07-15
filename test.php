<?php
session_start();
require_once('includes/constantes_utilitaires.php');
require_once('includes/bdd.php');
?>

<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/sb-admin-2.min.css">
</head>

<body>
    <!DOCTYPE html>
    <html lang="fr">

    <head>
        <meta charset="UTF-8">
        <title>Éditeur d'en-tête PDF</title>
        <style>
            .apercu {
                border: 1px solid #ccc;
                padding: 20px;
                margin-top: 20px;
                width: 400px;
            }

            label {
                display: block;
                margin-top: 10px;
            }

            input {
                width: 100%;
            }
        </style>
    </head>

    <body>

        <h2>Modifier l'en-tête</h2>

        <form id="formulaire" method="post" action="generate_pdf.php">
            <label>Nom de l’entreprise :</label>
            <input type="text" name="entreprise" id="entreprise" value="Service Plus">

            <label>Adresse :</label>
            <input type="text" name="adresse" id="adresse" value="123 Rue principale">

            <label>Date :</label>
            <input type="date" name="date" id="date" value="<?= date('Y-m-d') ?>">

            <br><br>
            <button type="submit">Générer le PDF</button>
        </form>

        <h3>Aperçu en direct :</h3>
        <div class="apercu" id="preview">
            <strong id="prev_entreprise">Service Plus</strong><br>
            <span id="prev_adresse">123 Rue principale</span><br>
            <span id="prev_date"><?= date('Y-m-d') ?></span>
        </div>

        <script>
            const champEntreprise = document.getElementById('entreprise');
            const champAdresse = document.getElementById('adresse');
            const champDate = document.getElementById('date');

            const prevEntreprise = document.getElementById('prev_entreprise');
            const prevAdresse = document.getElementById('prev_adresse');
            const prevDate = document.getElementById('prev_date');

            function updatePreview() {
                prevEntreprise.textContent = champEntreprise.value;
                prevAdresse.textContent = champAdresse.value;
                prevDate.textContent = champDate.value;
            }

            champEntreprise.addEventListener('input', updatePreview);
            champAdresse.addEventListener('input', updatePreview);
            champDate.addEventListener('input', updatePreview);
        </script>

    </body>

    </html>

</body>

</html>