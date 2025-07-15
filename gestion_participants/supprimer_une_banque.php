<?php
require_once(__DIR__ . '/../includes/bdd.php');

if (!isset($_GET['id_participant'])) {
    die("Identifiant du participant manquant.");
}

$id_participant = intval($_GET['id_participant']);

// Récupérer les comptes
$stmt = $bdd->prepare("SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant = ?");
$stmt->execute([$id_participant]);
$banques = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalBanques = count($banques);

// Définir la limite de suppression
$maxSuppression = ($totalBanques >= 3) ? 2 : (($totalBanques == 2) ? 1 : 0);

if ($totalBanques <= 1) {
    echo "Ce participant n'a pas assez de comptes pour en supprimer.";
    exit;
}
?>

<h3>Sélectionnez <?= $maxSuppression ?> compte<?= $maxSuppression > 1 ? 's' : '' ?> à supprimer :</h3>
<form method="POST" action="valider_suppression_banque.php">
    <input type="hidden" name="id_participant" value="<?= $id_participant ?>">

    <?php foreach ($banques as $b): ?>
        <div>
            <label>
                <input type="checkbox" name="comptes[]" value="<?= $b['id'] ?>" class="supp-checkbox">
                <?= htmlspecialchars($b['banque']) ?> - <?= htmlspecialchars($b['numero_compte']) ?>
            </label>
        </div>
    <?php endforeach; ?>

    <button type="submit">Supprimer</button>
</form>

<script>
// Limite de sélection côté client
document.querySelectorAll('.supp-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        const checked = document.querySelectorAll('.supp-checkbox:checked').length;
        const max = <?= $maxSuppression ?>;
        if (checked > max) {
            alert("Vous ne pouvez sélectionner que " + max + " compte<?= $maxSuppression > 1 ? 's' : '' ?> bancaire(s) à supprimer.");
            checkbox.checked = false;
        }
    });
});
</script>
