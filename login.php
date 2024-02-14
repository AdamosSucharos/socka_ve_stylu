<?php

$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
    
        $mysqli = require __DIR__ . "/connect.php";
        
        $email = $mysqli->real_escape_string($_POST["email"]);
        
        $sql = "SELECT * FROM user WHERE email = '$email'";
        $result = $mysqli->query($sql);
        
        $user = $result->fetch_assoc();
        
        if ($user) {
            

            if ($user["is_banned"] == 1) {
                $is_invalid = true;
                $ban_message = "Tvoj účet je zabanovaný.";
            } else {
                if (password_verify($_POST["password"], $user["password_hash"])) {
                    
                    session_start();
                    session_regenerate_id();
                    
                    $_SESSION["id"] = $user["user_id"];
                    
                    
                    header("Location: home.php");
                    exit;
                }
                $is_invalid = true;
            }
        } else {
            $is_invalid = true;
        }
        
    } else {
        $is_invalid = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: grey;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .card {
            width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-control {
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Login</h2>
        </div>
        <div class="card-body">
            <?php if ($is_invalid): ?>
                <div class="alert alert-danger" role="alert">
                    <?= isset($ban_message) ? $ban_message : "Invalid login" ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Log in</button>
            </form>
            <div class="mt-3 text-center">
                <a href="register.php">Register</a> <br>
                <a href="home.php">Domov</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
