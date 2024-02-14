<?php
session_start();

$mysqli = require __DIR__ . "/connect.php";

$categories = [];
$subcategories = [];

$userId = isset($_SESSION['id']) ? $_SESSION['id'] : null;

$user = []; 

if ($userId !== null) {
    $userSql = sprintf("SELECT * FROM user WHERE user_id = %d", $userId);
    $userResult = $mysqli->query($userSql);

    if ($userResult) {
        $user = $userResult->fetch_assoc();
    }
}

if (isset($_GET['referat_id'])) {
    $referat_id = $_GET['referat_id'];

    $sql = "SELECT referaty.*, category.category_name 
            FROM referaty 
            JOIN category ON referaty.category_id = category.category_id 
            WHERE referaty.referat_id = $referat_id";
    $result = $mysqli->query($sql);

    if ($result && $result->num_rows > 0) {
        $paperDetails = $result->fetch_assoc();
    } else {
        echo "Paper not found.";
    }

    $testCheckSql = "SELECT * FROM tests WHERE referat_id = $referat_id";
    $testCheckResult = $mysqli->query($testCheckSql);
    $testExists = $testCheckResult && $testCheckResult->num_rows > 0;

} else {
    echo "Invalid request.";
}

$result = $mysqli->query("SELECT * FROM category WHERE parent_category_id IS NULL");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$result = $mysqli->query("SELECT * FROM category WHERE parent_category_id IS NOT NULL");
while ($row = $result->fetch_assoc()) {
    $subcategories[$row['parent_category_id']][] = $row;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($paperDetails['title']) ? $paperDetails['title'] : ''; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .container {
            padding-top: 20px;
            margin-bottom: 80px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); 
        }
        .add-test-button,
        .view-test-button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
        }
        .add-test-button:hover,
        .view-test-button:hover {
            background-color: #0056b3;
            color: #fff;
        }
    </style>
</head>
<body>
    
<?php require_once("header.php"); ?>

<div class="container">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="home.php">Domov</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo isset($paperDetails['title']) ? $paperDetails['title'] : ''; ?></li>
        </ol>
    </nav>
    <h1><?php echo isset($paperDetails['title']) ? $paperDetails['title'] : ''; ?></h1>
    <?php if (!$testExists):
       if (!empty($user) && $user['user_id'] == $paperDetails['user_id']): ?>
        <form method="get">
            <a href="add_test.php?referat_id=<?php echo $referat_id; ?>" class="add-test-button">Pridaj test</a>
        </form>
    <?php endif; endif;?> 
    <p>Kategória: <?php echo isset($paperDetails['category_name']) ? $paperDetails['category_name'] : ''; ?></p>
    <p><?php echo isset($paperDetails['content']) ? $paperDetails['content'] : ''; ?></p>


    <?php if ($testExists && !empty($user)): ?>
        <a href="view_test.php?referat_id=<?php echo $referat_id; ?>" class="view-test-button">Zobraziť test</a>
    <?php endif; ?>
</div>

<footer class="text-center py-3 fixed-bottom">
    <p class="mb-0">© <?php echo date("Y"); ?> Referátový sklad. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
