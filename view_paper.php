<?php
session_start();

$mysqli = require __DIR__ . "/connect.php";

$categories = [];
$subcategories = [];

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

    // Check if a test exists for the given paper
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
    <link rel="stylesheet" href="style.css?rand<?php echo rand(1, 90); ?>">
    <title><?php echo $paperDetails['title'];?> </title>
</head>
<body>
    
<?php require_once("header.php"); ?>

<div class="container">
    <h1><?php echo $paperDetails['title']; ?>
    <?php if (isset($user) && $user['user_id'] == $paperDetails['user_id']): ?>
        <form method="get">
            <a href="add_test.php?referat_id=<?php echo $referat_id; ?>" class="add-test-button">Pridaj test</a>
        </form>
    <?php endif; ?>
    </h1>
    <p>Kategória: <?php echo $paperDetails['category_name']; ?></p>
    <p><?php echo $paperDetails['content']; ?></p>

    <!-- Display the button/link for viewing the test only if it exists -->
    <?php if (isset($user) && $user['user_id'] == $paperDetails['user_id'] && $testExists): ?>
        <a href="view_test.php?referat_id=<?php echo $referat_id; ?>" class="view-test-button">Zobraziť test</a>
    <?php endif; ?>
</div>
</body>
</html>
