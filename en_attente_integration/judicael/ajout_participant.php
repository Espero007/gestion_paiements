<?php
try {
    
    $pdo = new PDO('mysql:host=localhost;dbname=freelaance_benin', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    @$id_activite = $_GET["id_activite"];

    $query = "SELECT id_type_activite FROM activites WHERE id_activite = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id_activite]);
    $activite = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $titre = $_POST["titre"];
        $diplome = $_POST["diplome"];
        $nombre_jours = $_POST["nombre_jours"] ?? NULL;
        $nombre_taches = $_POST["nombre_taches"] ?? NULL;

        
        $insertQuery = "INSERT INTO participations (id_activite, id_titre, id_diplome, nombre_jours, nombre_taches)
                        VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $pdo->prepare($insertQuery);
        $insertStmt->execute([$id_activite, $titre, $diplome, $nombre_jours, $nombre_taches]);

        echo "<p> Participation enregistrée avec succès !</p>";
    }

    if ($activite) {
        $id_type_activite = $activite["id_type_activite"];
?>
        <form method="post" action="">
            <input type="hidden" name="id_activite" value="<?= htmlspecialchars($id_activite) ?>">
            <label for="titre">Titre :</label>
            <input type="text" name="id_titre" id="titre" required><br>

            <label for="diplome">Diplôme :</label>
            <input type="text" name="id_diplome" id="diplome" required><br>

            <?php if ($id_type_activite >= 2) { ?>
                <label for="nombre_jours">Nombre de jours :</label>
                <input type="number" name="nombre_jours" id="nombre_jours" required><br>
            <?php } ?>

            <?php if ($id_type_activite == 3) { ?>
                <label for="nombre_taches">Nombre de tâches :</label>
                <input type="number" name="nombre_taches" id="nombre_taches" required><br>
            <?php } ?>

            <input type="submit" value="Soumettre">
        </form>
<?php
    } else {
        echo "<p> Activité non trouvée.</p>";
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
