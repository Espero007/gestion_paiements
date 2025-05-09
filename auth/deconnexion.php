<?php

session_start();
$_SESSION['deconnexion'] = true;
header('location:connexion.php');