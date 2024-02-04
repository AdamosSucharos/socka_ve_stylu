<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="style.css?rand<?php echo rand(1, 90); ?>">

    <title>Document</title>
</head>
<body>
    <?php
        session_start();

        require_once("header.php");
        require_once("connect.php");

    ?>
</body>
</html>