<?php
    session_start();
    require_once(__DIR__.'/../../includes/bdd.php');
    require_once(__DIR__.'/modifier_profile.php');

    $user_id = $_SESSION['user_id'];

    $smt = $bdd->prepare("SELECT nom, prenoms, email FROM connexion WHERE user_id = ?");
    $smt->execute([$user_id]);
    $user = $smt->fetch();
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Profile
    </title>

    <link rel="stylesheet" href="../../assets/vendor/fontawesome-free/css/all.min.css">
    <link href="../../assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body>
    
<?php if ($user) : ?>
    <form action="" method="POST" class=" container">
        <?php
            if (isset($info)) {
            
               echo ' <div class="alert alert-success">'. htmlspecialchars($info).'</div>';
               unset($info);
            
            }else { echo '';}
        ?>

        <div>
            <label for="nom" class=" form-label"> Nom </label>
            <input type="text" class="form-control" name="nom" id="nom" value= "<?= htmlspecialchars($user['nom'])  ?>" required>
        </div>

        <div>
            <label for="prenom" class=" form-label"> Prénoms </label>
            <input type="text" class="form-control" name="prenom" id="prenom" value="<?=  htmlspecialchars($user['prenoms'])?>" required>
        </div>

        <div>
            <label for="email" class=" form-label"> Email </label>
            <input type="email" class="form-control" name="email" id="email" value ="<?= htmlspecialchars($user['email'])?>" required>
            <p> <small class=" text-danger"> <?= isset($erreur_mail)? htmlspecialchars($erreur_mail): ''; unset($erreur_mail)?> </small> </p> 
        </div>

        <div>
            <label for="password" class=" form-label"> Mot de passe</label>
            <input type="password" class="form-control" name="password" id ="password">
            <p> <small class=" text-danger"> <?= isset($erreur)? htmlspecialchars($erreur): ''; unset($erreur)?> </small> </p> 
        </div>

        <button type="submit" name="enregistrer" class=" btn btn-outline-success my-4">Enregistrer</button>
        
    </form>

    <?php endif; ?>
</body>
</html>