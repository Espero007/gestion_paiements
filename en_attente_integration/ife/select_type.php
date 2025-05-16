<?php
session_start();

require_once(__DIR__ . '/variables.php');
//require_once(__DIR__ . '/header.php');

$loggedUser = $_SESSION['loguser'] ?? null;
if (!$loggedUser) {
    header('Location: connexion.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_submitted'])) {
    $type = $_POST['type'] ?? '';
    if (empty($type)) {
        $errors['type'] = "Veuillez sélectionner un type d'activité.";
    } elseif (!in_array($type, ['1', '2', '3'])) {
        $errors['type'] = "Type d'activité invalide.";
    } else {
        $_SESSION['type_activite'] = $type;
        header('Location: createActivity.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sélectionner le type d'activité</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="container py-4">
    <?php echo "<h2>Bienvenue sur notre site <i style='color:blue'>" . $loggedUser["nom"] . " " . $loggedUser["prenoms"] . "</i></h2>"; ?>
    <h2>Sélectionner le type d'activité</h2>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <strong>Erreur :</strong> <?= htmlspecialchars($errors['type'] ?? 'Une erreur s\'est produite.') ?>
        </div>
    <?php endif; ?>
    <form method="POST" id="typeForm">
        <input type="hidden" name="form_submitted" value="1">
        <div class="mb-3">
            <label class="form-label">Type d'activité</label>
            <select name="type" class="form-control" required>
                <option value="">Sélectionnez un type</option>
                <option value="1">Type 1</option>
                <option value="2">Type 2</option>
                <option value="3">Type 3</option>
            </select>
            <small class="text-danger"><?= $errors['type'] ?? '' ?></small>
        </div>
        <button type="submit" class="btn btn-primary" id="submitButton">Continuer</button>
    </form>

    <script>
        // Désactiver le bouton après le premier clic
        document.getElementById('typeForm').addEventListener('submit', function() {
            document.getElementById('submitButton').disabled = true;
        });
    </script>
</body>
</html>