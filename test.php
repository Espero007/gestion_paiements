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
        $date = new DateTime();
        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Africa/Lagos',
            IntlDateFormatter::GREGORIAN
        );

        echo $formatter->format($date);
    ?>
</body>

<!-- <div class="container-md">
    <div class="alert alert-danger mt-4 text-center">
        Voici une information
    </div>
</div> -->

</html>