<?php
session_start();
require_once(__DIR__ . "/../database.php");
require_once(__DIR__ . "/../variables.php");

if($_SERVER["REQUEST_METHOD"] === "POST"){
    if (
        isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)
        && !empty($_POST["email"])
    ) {
        $checkEmail = $mySqlClient->prepare("SELECT user_id FROM connexion WHERE email = :email");
        $checkEmail->execute([
            "email" => $_POST["email"],
        ]);
    
        if($checkEmail->rowCount() > 0){
    
            if($_POST["new_password"] === $_POST["password"]){
                    if(isset($_POST["new_password"],$_POST["password"]) && !empty($_POST["new_password"]) && !empty($_POST["password"]) && strlen($_POST["new_password"]) > 5 && preg_match('/^[A-Z]/',$_POST["password"]) && !preg_match('/\d/',$_POST["password"])){
                    $password = $mySqlClient->prepare("UPDATE connexion SET Password = :Password WHERE email=:email");
                    $password-> execute([
                        "Password" => $_POST["new_password"],
                        "email" => $_POST["email"],
                    ]);
                    header("Location:connexion.php");
                    exit;
                }else{
                    $_SESSION["password"]="Le mot de passe doit contenir au moins 06 caractères; commencer par une lettre majuscule et contenir au moins un chiffre";
                    
                }
            
            }else{
                $_SESSION["invalid_password"]=" Mot de passe incorrect";
                
            }
        }
        else{
            $_SESSION["email"] = "Auccun n'utilisateur avec cet email";
           
        }
    }else{
        $_SESSION["error_message"] = "veillez remplir conveablement le formulaire";
        
    }
    
    
}

?>