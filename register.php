<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $err = [];

    if (empty($_POST["name"])) {
        $err[] = "Meno je povinné!";
    }

    if (empty($_POST["email"])) {
        $err[] = "Email je povinný!";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $err[] = "Zadaj správny email!";
    }

    if (empty($_POST["password"])) {
        $err[] = "Heslo je povinné";
    } elseif (strlen($_POST["password"]) < 8) {
        $err[] = "Heslo musí mať aspoň 8 znakov!";
    } elseif (!preg_match("/[a-z]/i", $_POST["password"])) {
        $err[] = "Heslo musí obsahovať aspoň 1 písmeno!";
    } elseif (!preg_match("/[0-9]/", $_POST["password"])) {
        $err[] = "Heslo musí obsahovať aspoň 1 číslo!";
    }

    if (empty($_POST["password_confirmation"])) {
        $err[] = "Potvrdenie hesla je povinné!";
    } elseif ($_POST["password"] !== $_POST["password_confirmation"]) {
        $err[] = "Hesla sa musia zhodovať!";
    }

    if (!empty($err)) {
        $_SESSION['err'] = $err;
        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    $password_hash = password_hash($_POST["password"], PASSWORD_DEFAULT);

    require_once("connect.php");

    $checkSql = "SELECT email FROM user WHERE email = ?";
    $checkStmt = $mysqli->stmt_init();

    if (!$checkStmt->prepare($checkSql)) {
        die("SQL error: " . $mysqli->error);
    }

    $checkStmt->bind_param("s", $_POST["email"]);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $_SESSION['err'] = ["Email je už zaregistrovaný!"];
        header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    }

    $sql = "INSERT INTO user (name, email, password_hash)
            VALUES (?, ?, ?)";

    $stmt = $mysqli->stmt_init();

    if (!$stmt->prepare($sql)) {
        die("SQL error: " . $mysqli->error);
    }

    $stmt->bind_param("sss", $_POST["name"], $_POST["email"], $password_hash);
    if ($stmt->execute()) {
        unset($_SESSION['err']); 
        $_SESSION['email'] = $_POST["email"];
        header("Location: login.php");
        exit;
    } else {
        die($mysqli->error . " " . $mysqli->errno);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

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
        .error-message {
            color: #dc3545;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<style>
        body{
            background-color: gray;
        }
        .card-body{
            background-color: f5f5f5;
        }
        
    </style>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Register</h2>
        </div>
        <div class="card-body">
            <?php
            if (isset($_SESSION['err'])) {
                $errorMessages = $_SESSION['err'];
                echo '<div class="error-message"><em>' . implode('<br>', $errorMessages) . '</em></div>';
                unset($_SESSION['err']); 
            }
            ?>
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="signup" novalidate>
                <div class="mb-3">
                    <label for="name" class="form-label">Prezývka:</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Heslo:</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Potvrď heslo:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                </div>
                <button type="submit" name="submit" class="btn btn-primary">Register</button>
            </form>
            <div class="mt-3 text-center">
                <a href="login.php">Login</a> <br>
                <a href="home.php">Domov</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
