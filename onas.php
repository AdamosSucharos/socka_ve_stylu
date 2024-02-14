<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <title>Document</title>
    <style>
        html {
            position: relative;
            min-height: 100%;
        }
        body {
            margin-bottom: 60px; 
        }
        footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 60px; 
            background-color: lightgrey; 
            color: black; 
        }
    </style>
</head>
<body>
    <?php
        session_start();

        require_once("header.php");
        require_once("connect.php");

    ?>

    <div class="container">
        <h1>Vitaj v Sklade!</h1>
        <p>Táto webová stránka je vašou platformou na nahrávanie a sprístupňovanie dokumentov, testov a ďalšie...</p>
        <h2>
    </div>













<footer class="text-center py-3 fixed-bottom">
    <p class="mb-0">© <?php echo date("Y"); ?> Referátový sklad. All rights reserved.</p>
</footer>
</body>
</html>