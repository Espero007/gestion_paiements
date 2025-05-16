<?php
    @$keywords = $_GET["keywords"];
    @$valider = $_GET["valider"];

    if (isset($valider) && !empty(trim($keywords))) {
        $words = explode(" ", trim($keywords));

        $pdo = new PDO('mysql:host=localhost;dbname=freelaance_benin', 'root', "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $kw = [];
        foreach ($words as $word) {
            $kw[] = "(description LIKE ? OR nom LIKE ?)";
        }

        $query = "SELECT nom, description FROM activites WHERE " . implode(" OR ", $kw);
        $stmt = $pdo->prepare($query);

        $params = [];
        foreach ($words as $word) {
            $params[] = "%$word%";
            $params[] = "%$word%";
        }

        $stmt->execute($params);
        $tab = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $afficher = "oui";
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche d'Activités</title>
</head>
<body>
    <form name="fo" method="get" action="">
        <input type="text" name="keywords" value="<?= htmlspecialchars($keywords) ?>" placeholder="Mots-clés" />
        <input type="submit" name="valider" value="Rechercher" />
    </form>

   <?php if (@$afficher == "oui") { ?>
    <div id="resultats">
        <div id="nbr"> <?= count($tab) . " " . (count($tab) > 1 ? "résultats trouvés" : "résultat trouvé") ?></div>
        <ol>
            <?php foreach ($tab as $result) { ?>
            <li><strong><?= htmlspecialchars($result["nom"]) ?>:</strong> <?= htmlspecialchars($result["description"]) ?></li>
            <?php } ?>
        </ol>
    </div>
    <?php } ?>
</body>
</html>
