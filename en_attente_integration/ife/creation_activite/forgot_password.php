<?php 
        require_once(__DIR__."/submit/submit_forgot.php");
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="container py-4">
    
<main>
    <h2> Mot de passe oublé</h2>
    

    <?php
    if (isset($_SESSION["error_message"])) {
    ?>
    <div style="color:red"> <?php echo $_SESSION["error_message"];
    unset($_SESSION["error_message"]); ?>
    </div>
    <?php
    }
    ?>
     <div class="containers">
    <form action="" method="POST">
        <p> <label for="email"> Insérer votre email </label> </p>
        <input type="email" name="email" id ="email" placeholder="votre_nom@gmail.com" value="<?php htmlspecialchars($_POST["email"] ?? "")?>"> <br>
        <small  style = "color:red"> <?php if ((isset($_SESSION["email"]))){
            echo $_SESSION["email"];
            unset($_SESSION["email"]);
        } ?> </small>


            <p> <label for="new_password"> Nouveau mot de passe</label></p>
            <input type="password" name="new_password" id ="new_password" value="<?php htmlspecialchars($_POST["new_password"] ?? "") ?>"> <br>
            <small style = "color:red"> <?php  if ((isset($_SESSION["password"]))){
                echo $_SESSION["password"];
                unset($_SESSION["password"]);
        } ?> </small>


            <p> <label for="password"> Confirmer le mot de passe</label></p>
            <input type="password" name="password" id ="password" value="<?php htmlspecialchars($_POST["password"] ?? "") ?> "> <br>
            <small  style = "color:red"> <?php  if ((isset($_SESSION["invalid_password"]))){
                echo $_SESSION["invalid_password"];
                unset($_SESSION["invalid_password"]);
        } ?> </small>        

        <p> <button type="submit"> Envoyer </button> </p>
    </form>
    </div>
</main>
</body>
</html>