<?php

if (!valider_id('get', 'id', '')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_participant = dechiffrer($_GET['id']);

// Récupérer les comptes
$stmt = $bdd->prepare("
SELECT id, banque, numero_compte, chemin_acces, id_rib, p.nom, p.prenoms
FROM informations_bancaires ib
INNER JOIN fichiers ON fichiers.id_fichier = ib.id_rib 
INNER JOIN participants p ON p.id_participant = ib.id_participant
WHERE ib.id_participant = ?");
$stmt->execute([$id_participant]);
$banques = $stmt->fetchAll(PDO::FETCH_ASSOC);
$numeros_comptes = [];

// Compter combien de comptes a ce participant
$stmtTotal = $bdd->prepare("SELECT COUNT(*) AS total FROM informations_bancaires WHERE id_participant = ?");
$stmtTotal->execute([$id_participant]);
$totalBanques = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
$stmtTotal->closeCursor();

if ($totalBanques == 0) {
    redirigerVersPageErreur();
}

// Calculer la limite autorisée
// $maxSuppression = ($totalBanques >= 3) ? 2 : (($totalBanques == 2) ? 1 : 0);

foreach ($banques as $index => $banque) {
    $banques[$index]['numero_compte'] = strtolower($banque['numero_compte']);
    $numeros_comptes[] = $banques[$index]['numero_compte'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['suppressionBanque'])) {
        $erreurs['confirmation'] = true;
    } else {
        // Les validations associées au compte bancaire choisi
        // On s'assure tout d'abord que l'utilisateur n'a pas sélectionné tous les comptes bancaires là
        if (isset($_POST['banque'])) {
            $comptes_choisis = $_POST['banque'];

            if (count($comptes_choisis) != $totalBanques) {
                // On a pas sélectionné tous les comptes du gars
                // On s'assure à présent que parmi ces comptes il n'y en a pas déjà un qui est associé à une activité
                foreach ($comptes_choisis as $compte) {
                    if (in_array($compte, $numeros_comptes)) {
                        $stmt = $bdd->query('
                        SELECT p.id, a.nom
                        FROM participations p
                        INNER JOIN activites a on a.id = p.id_activite
                        WHERE id_compte_bancaire IN
                        (SELECT id FROM informations_bancaires WHERE numero_compte=\'' . $compte . '\')
                        ');

                        if ($stmt->rowCount() != 0) {
                            $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
                            $stmt->closeCursor();
                            $erreurs['compte_deja_utilise'] = 'Vous ne pouvez pas supprimer le compte (<strong>' . strtoupper($compte) . '</strong>) car le participant a été associé à l\'activité "<strong>' . $resultat['nom'] . '</strong>" avec ce dernier. <a href="/gestion_participants/lier_participant_activite.php?modifier=' . $resultat['id'] . '">Cliquez ici</a> pour modifier les informations de cette liaison puis revenez supprimer le compte.';
                        }
                    } else {
                        redirigerVersPageErreur(404, $_SESSION['previous_url']);
                    }
                }

                if (!isset($erreurs)) {
                    foreach ($comptes_choisis as $compte) {
                        // On supprime le fichier dans notre dossier d'upload
                        foreach ($banques as $banque) {
                            if ($banque['numero_compte'] == $compte) {
                                $chemin_fichier = $banque['chemin_acces'];
                                $id_rib = $banque['id_rib'];
                                $id = $banque['id'];
                            }
                        }
                        unlink($chemin_fichier);

                        // On supprime la ligne correspondant au compte dans la table fichiers
                        $stmtDeleteRib = $bdd->prepare("DELETE FROM fichiers WHERE id_fichier = ?");
                        $stmtDeleteRib->execute(['id_rib']);

                        // On supprime enfin la ligne dans la table informations_bancaires
                        $stmtDeleteCompte = $bdd->prepare("DELETE FROM informations_bancaires WHERE id = ?");
                        $stmtDeleteCompte->execute([$id]);
                    }

                    arrangerRibs($id_participant); // Pour renommer adéquatement les pdfs_ribs

                    // On redirige vers la page précédente avec un message de succès
                    $participant = $banques['nom'] . ' ' . $banques['prenoms'];
                    $_SESSION['comptes_supprimes'] = count($comptes_choisis) > 1 ? 'Les comptes bancaires de <strong>' . $participant . '</strong> ont été supprimés avec succès !' : 'Le compte bancaire de <strong>' . $participant . '</strong> a été supprimé avec succès !';
                    header('location:' . $_SESSION['previous_url']);
                    exit;
                }
            } else {
                $erreurs['trop_de_comptes'] = true;
            }
        } else {
            $erreurs['pas_de_choix'] = true;
        }
    }
}
