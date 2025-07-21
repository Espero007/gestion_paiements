<?php
session_start();
require_once(__DIR__ . "/../../includes/bdd.php");
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

if($_SERVER["REQUEST_METHOD"] === "POST"){
    if (
        isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)
        && !empty($_POST["email"])
    ) {
        $checkEmail = $bdd->prepare("SELECT user_id FROM connexion WHERE email = :email");
        $checkEmail->execute([
            "email" => $_POST["email"],
        ]);
    
        if($checkEmail->rowCount() > 0){
    
            if($_POST["new_password"] === $_POST["password"]){
                    if(isset($_POST["new_password"],$_POST["password"]) && !empty($_POST["new_password"]) && !empty($_POST["password"]) && strlen($_POST["new_password"]) > 5 && preg_match('/^[A-Z]/',$_POST["password"]) && preg_match('/\d/',$_POST["password"])){
                    $password = $bdd->prepare("UPDATE connexion SET password = :Password WHERE email=:email");
                    $password->execute([
                        "Password" => password_hash($_POST["new_password"],PASSWORD_DEFAULT),
                        "email" => $_POST["email"],
                    ]);
                    header('Location:'.generateUrl('connexion'));
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