<?php
if (empty($_POST['ifu'])) {
    header("Location:sup.php");
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;dbname=freelaance_benin', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   
    $ifu = $_POST['ifu'];
    $stmt = $pdo->prepare("SELECT id_participant FROM participants WHERE matricule_ifu = ?");
    $stmt->execute([$ifu]);
    $participant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$participant) {
        echo "<p>⚠️ Participant non trouvé.</p>";
        exit;
    }

    $id_participant = $participant["id_participant"];

    $pdo->beginTransaction();

    $tables = [
        "informations_bancaires",
        "fichiers",
        "participations",
        "connexion",
        "participants"
    ];

    foreach ($tables as $table) {
        $deleteStmt = $pdo->prepare("DELETE FROM $table WHERE id_participant = ?");
        $deleteStmt->execute([$id_participant]);
    }

    $pdo->commit();

    echo "<p>Participant supprimé avec succès de toutes les tables.</p>";

} catch (Exception $e) {
    $pdo->rollBack();
    echo " Erreur : " . $e->getMessage();
}
?>
