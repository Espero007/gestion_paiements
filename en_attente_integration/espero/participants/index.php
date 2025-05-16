<?php
session_start();
require_once(__DIR__ . '/../../../includes/bdd.php');


$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
// Récupération du nom de domaine + port si nécessaire
$host = $_SERVER['HTTP_HOST'];
// Récupération du chemin URI
$request_uri = $_SERVER['REQUEST_URI'];
// URL complète
$current_url = $protocol . $host . $request_uri;

$stmt = "SELECT id_participant, nom, prenoms, matricule_ifu FROM participants WHERE id_user=".$_SESSION['user_id']." ORDER BY id_participant DESC";
// echo $stmt;
$resultats = $bdd->query($stmt);

if (!$resultats) {
    redirigerVersPageErreur(500, $current_url);
}

$participants = $resultats->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['rechercher'])) {
    $valeur = trim($_POST['valeur']);
    if (!empty($valeur)) {
        // echo "Voici la valeur à rechercher : " . $valeur;
        $stmt = $bdd->prepare("SELECT id_participant, nom, prenoms, matricule_ifu FROM participants WHERE nom LIKE ? OR prenoms LIKE ? OR matricule_ifu LIKE ?");
        $search = "%$valeur%";
        $resultats = $stmt->execute([$search, $search, $search]);

        if (!$resultats) {
            redirigerVersPageErreur(500, $current_url);
        }
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $resultats = []; // tableau vide
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
</head>

<body>
    <div class="container-md">
        <h4>Liste des participants</h4>
    </div>
    <form action="" method="post">
        <input size=50 type="text" name="valeur" id="rechercher" placeholder="Entrez la valeur à rechercher..."
            <?php if (isset($_POST['rechercher'])) {
                echo "value =" . htmlspecialchars($_POST['valeur']);
            } ?>>
        <button type="submit" name="rechercher">Rechercher</button>
    </form>
    <br>

    <?php if (!isset($_POST['rechercher'])) : ?>
        <!-- On effectue pas de recherche -->
        <?php if (empty($participants)) : ?>
        <!-- Aucun participant ajouté -->
        <p>Vous n'avez encore ajouté aucun participant</p>
        <a href="ajouter_participant.php">Ajoutez-en !</a>
    <?php else: ?>
        <?php foreach ($participants as $participant) : ?>
            <!-- <hr> -->

            <!-- <span>###</span> -->
            <div class="groupe">
                <strong>Nom</strong>
                <span> : <?php echo $participant['nom']; ?></span>
            </div>

            <div class="groupe">
                <strong>Prénom(s)</strong>
                <span> : <?php echo $participant['prenoms']; ?></span>
            </div>

            <div class="groupe">
                <strong>Matricule/IFU</strong>
                <span> : <?php echo $participant['matricule_ifu']; ?></span>
            </div>

            <br>
            <div class="boutons">
                <button>Gérer</button>
                <a href="modifier_informations.php?id_participant=<?php echo $participant['id_participant']; ?>"><button>Modifier les informations</button></a>
            </div>

            <br>
        <?php endforeach; ?>
    <?php endif; ?>
<?php else: ?>
    <hr>
    <!-- On effectue une recherche -->
    <?php if (empty($resultats)) : ?>
        <!-- Pas de résultats trouvés -->
        <p>Aucun résultat n'a été trouvé pour : "<strong><?php echo htmlspecialchars($_POST['valeur']); ?></strong>"</p>
    <?php else: ?>
        <p><strong><?php echo count($resultats); ?></strong> résultat(s) trouvé(s) pour : "<strong><?php echo htmlspecialchars($_POST['valeur']); ?></strong>"</p>
        <?php foreach ($resultats as $resultat) : ?>

            <!-- Des résultats trouvés -->

            <div class="groupe">
                <strong>Nom</strong>
                <span> : <?php echo $resultat['nom']; ?></span>
            </div>

            <div class="groupe">
                <strong>Prénom(s)</strong>
                <span> : <?php echo $resultat['prenoms']; ?></span>
            </div>

            <div class="groupe">
                <strong>Matricule/IFU</strong>
                <span> : <?php echo $resultat['matricule_ifu']; ?></span>
            </div>

            <br>
            <div class="boutons">
                <button>Gérer</button>
                <a href="modifier_informations.php?id_participant=<?php echo $resultat['id_participant']; ?>"><button>Modifier les informations</button></a>
            </div>

            <br>

        <?php endforeach; ?>


    <?php endif; ?>
    <!-- Lien pour revenir à la page d'index -->
    <a href="index.php">Annuler la recherche</a>
<?php endif; ?>



<!-- <button></button> -->

</body>

</html>