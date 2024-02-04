<?php
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
    header("Location: register.php?err=" . urlencode(implode(",", $err)));
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
    header("Location: register.php?err=" . urlencode("Email je už zaregistrovaný!"));
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
    header("Location: login.php");
    exit;
} else {
    die($mysqli->error . " " . $mysqli->errno);
}
?>
