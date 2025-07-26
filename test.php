<?php
session_start();
require_once('includes/constantes_utilitaires.php');
require_once('includes/bdd.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/sb-admin-2.min.css">
</head>

<body>
    <style>
        body {
            padding: 20px;
        }

        td {
            text-align: center;
            line-height: 16px;
            border: 1px solid #000;
            border-top: none;
            border-bottom: none;
        }

        tr {
            border: 1px solid #000;
        }
    </style>
    <table width="100%" cellpadding="5">
        <tbody>
            <tr>
                <td>Bonjour</td>
                <td>Element 2</td>
                <td>Element 3</td>
            </tr>
            <tr>
                <td>Bonjour</td>
                <td>Element 2</td>
                <td>Element 3</td>
            </tr>
            <tr>
                <td>Bonjour</td>
                <td>Element 2</td>
                <td>Element 3</td>
            </tr>
        </tbody>
    </table>

</body>

</html>