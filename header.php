<?php

$mysqli = require __DIR__ . "/connect.php";
$user = []; 


if (isset($_SESSION["id"])) {
    $userId = $_SESSION['id'];


    $userSql = sprintf("SELECT * FROM user WHERE user_id = %d", $userId);
    $userResult = $mysqli->query($userSql);


    if ($userResult) {
        $user = $userResult->fetch_assoc();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container-fluid {
            text-align: center;
            background-color: gray; 
            color: #0056b3;
        }
        .navbar-brand {
            font-size: 1.7rem;
        }

        .nav-link {
            font-size: 1.2rem;
        }
        .nav-link:hover {
            background-color: #e0e0e0; 
        }
        #user-icon {
            font-size: 1.2rem;
            margin-right: 10px;
        }

        .btn-outline-primary,
        .btn-outline-info,
        .btn-outline-success,
        .btn-outline-danger {
            background-color: darkgrey;
            border-color: #0056b3;
            margin-left: 10px;
        }
        .my-custom-button {
            background-color: #007bff; 
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.2s ease-in-out;
        }
        button.navbar-toggler
        {
            background-color: #007bff ;
        }
        button.btn-outline-danger,
        button.btn-outline-success,
        .btn {
            text-decoration: none; 
        }
        button.btn-outline-danger {
            background-color: lightcoral;
        }
        button.btn-outline-success {
            background-color: #e0e0e0;
            color: black;
        }
        .my-custom-button:hover {
            background-color: #e0e0e0;
        }

        header {
            border-bottom: 2px solid black; 
        }
    </style>
</head>
<body>

<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-primary p-0 m-0">
        <div class="container-fluid">
        <a class="navbar-brand" href="home.php"><i class="fas fa-book"></i> Domov</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="onas.php">O nás</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="kontakt.php">Kontakt</a>
                    </li>
                    <?php if (!empty($user)): ?>
                        <li class="nav-item">
                            <a id="user-icon" class="nav-link" href="profile.php"><i class="fas fa-user"></i> <?php echo $user["name"]; ?></a>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-danger"><a href="logout.php">Odhlásiť</a></button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-success"><a href="referaty.php">Pridaj referáty</a></button>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <button class="btn btn-outline-primary custom-search-button"><a href="register.php">Register</a></button>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-primary custom-search-button"><a href="login.php">Prihlásiť sa</a></button>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
