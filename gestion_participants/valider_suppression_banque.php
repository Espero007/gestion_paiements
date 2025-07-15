<?php
require_once(__DIR__ . '/../includes/bdd.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_participant = intval($_POST['id_participant'] ?? 0);
    $comptes_a_supprimer = $_POST['comptes'] ?? [];

    // Vérifier la validité
    if ($id_participant <= 0 || empty($comptes_a_supprimer)) {
        header("Location: choisir_banque_a_supprimer.php?id_participant=$id_participant&erreur=selection");
        exit;
    }

    // Compter combien de comptes a ce participant
    $stmtTotal = $bdd->prepare("SELECT COUNT(*) AS total FROM informations_bancaires WHERE id_participant = ?");
    $stmtTotal->execute([$id_participant]);
    $totalBanques = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

    // Calculer la limite autorisée
    $maxSuppression = ($totalBanques >= 3) ? 2 : (($totalBanques == 2) ? 1 : 0);

    // Vérification côté serveur de la limite
    if (count($comptes_a_supprimer) > $maxSuppression) {
        header("Location: choisir_banque_a_supprimer.php?id_participant=$id_participant&erreur=limite_depassee");
        exit;
    }

    try {
        $bdd->beginTransaction();

        foreach ($comptes_a_supprimer as $id_compte) {
            $id_compte = intval($id_compte);

            // Récupérer l’id du fichier RIB pour suppression
            $stmtFichier = $bdd->prepare("SELECT id_rib FROM informations_bancaires WHERE id = ? AND id_participant = ?");
            $stmtFichier->execute([$id_compte, $id_participant]);
            $ribData = $stmtFichier->fetch(PDO::FETCH_ASSOC);

            if ($ribData) {
                // Supprimer le compte bancaire
                $stmtDeleteCompte = $bdd->prepare("DELETE FROM informations_bancaires WHERE id = ?");
                $stmtDeleteCompte->execute([$id_compte]);

                // Supprimer le fichier RIB associé
                $stmtDeleteRib = $bdd->prepare("DELETE FROM fichiers WHERE id_fichier = ?");
                $stmtDeleteRib->execute([$ribData['id_rib']]);
            }
        }

        $bdd->commit();

        header("Location: gerer_participant.php?id=$id_participant&banque_supprimee=ok");
        exit;

    } catch (PDOException $e) {
        $bdd->rollBack();
        header("Location: supprimer_une_banque.php?id_participant=$id_participant&erreur=sql");
        exit;
    }
} else {
    header("Location: supprimer_une_banque.php?erreur=acces");
    exit;
}
