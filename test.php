<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
</head>

<body>

    <?php
    var_dump('bob@example.com', FILTER_VALIDATE_EMAIL);
    echo '<br>';
    var_dump('https://example.com', FILTER_VALIDATE_URL);
    echo '<br>';
    var_dump('0755', FILTER_VALIDATE_INT);
    echo '<br>';
    var_dump('011', FILTER_VALIDATE_INT);
    ?>
</body>

<!-- <div class="container-md">
    <div class="alert alert-danger mt-4 text-center">
        Voici une information
    </div>
</div> -->

</html>