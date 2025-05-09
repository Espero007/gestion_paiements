<!DOCTYPE html>
<html>
    <head>
        <title>Suppression d'un candidat</title>
    </head>

    <body>
        <?php
        if(empty($_POST['ifu']))
        {
            header("Location:sup.php");
        }

        $bddPDO = new PDO('mysql:host=localhost;dbname=participant', 'root', '');
        $bddPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if(!isset($_POST['ifu']))
        {
            $ifu = $_POST['ifu'];
            $requete = "SELECT * FROM participant WHERE IFU ='$ifu'";
            $result = $bddPDO->query($requete);
            $data = $result->fetch(PDO::FETCH_ASSOC);

            $result->closeCursor();
        }
        else 
        {
            $ifu = $_POST['ifu'];
            $requete = "DELETE FROM participant WHERE ifu ='$ifu'";
            $result = $bddPDO->exec($requete);

            if(!$result)
            {
                echo "Il y a un souci";
            }
            else
            {
                echo "Le participant a ete bien supprime";
            }
        }
        ?>
    </body>

</html>