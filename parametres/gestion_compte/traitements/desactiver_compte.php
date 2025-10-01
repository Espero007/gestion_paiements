<?php
session_start();
require_once(__DIR__ . '/../../../includes/bdd.php');
$confirmation = true;

if (isset($_POST['supprimerCompte'])) {
    if (!isset($_POST['suppressionCompte'])) {
        // $confirmation = false;
        $erreurs['suppression_non_confirmee'] = true;
    } else {
        $user_id = $_SESSION['user_id'];
        // 1- Supprimer de la bdd les activités de l'utilisateur
        $stmt = $bdd->query('DELETE FROM activites WHERE id_user=' . $user_id);

        // 2- Récupérer et Supprimer les fichiers qui ont été uploadés par l'utilisateur aussi dans le dossier d'upload que dans la bdd
        $stmt = $bdd->query('SELECT * FROM fichiers f WHERE f.id_fichier IN ( SELECT f.id_fichier FROM fichiers f INNER JOIN informations_bancaires ib ON ib.id_rib = f.id_fichier INNER JOIN participants p ON p.id_participant = ib.id_participant WHERE p.id_user = ' . $user_id . ')');
        $fichiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($fichiers as $fichier) {
            if (file_exists($fichier['chemin_acces'])) {
                unlink($fichier['chemin_acces']);
            }
            $bdd->query('DELETE FROM fichiers WHERE id_fichier=' . $fichier['id_fichier']);
        }

        // Toujours dans le volet suppression de fichiers, on supprime aussi les photos de profil s'il y en a
        $stmt = $bdd->query('SELECT photo_profil FROM connexion WHERE user_id=' . $user_id);
        $chemin = $stmt->fetch(PDO::FETCH_NUM)[0];
        $stmt->closeCursor();
        if ($chemin && file_exists($chemin)) {
            unlink($chemin);
        }

        // 3- Supprimer les participants
        $stmt = $bdd->query('DELETE FROM participants WHERE id_user=' . $user_id);

        // 4- Supprimer ses informations dans la table de cookie (token_souvenir)
        $bdd->query('DELETE FROM token_souvenir WHERE user_id=' . $user_id);

        $stmt = $bdd->query('SELECT * FROM connexion WHERE user_id=' . $_SESSION['user_id']);
        $utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $utilisateur = $utilisateur[0];

        if ($utilisateur) {
            $smt = $bdd->prepare("DELETE FROM connexion WHERE user_id =?");
            $smt->execute([$_SESSION['user_id']]);

            header('Location: ../../../index.php');
            exit();
        }
    }

    $_SESSION['erreurs'] = $erreurs;
    header('location:../voir_profil.php');
}
