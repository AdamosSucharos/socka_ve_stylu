<?php
session_start();
$mysqli = require __DIR__ . "/connect.php";

if (isset($_GET['referat_id'])) {
    $referat_id = $_GET['referat_id'];

    // Check if the logged-in user is the author of the paper
    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];

        $check_author_sql = "SELECT * FROM referaty WHERE referat_id = $referat_id AND user_id = $user_id";
        $check_author_result = $mysqli->query($check_author_sql);

        if ($check_author_result && $check_author_result->num_rows > 0) {
            // The logged-in user is the author of the paper
            // Display the form for adding a test
        } else {
            echo "You do not have permission to add a test for this paper.";
        }
    } else {
        echo "You must be logged in to add a test.";
    }
} else {
    echo "Invalid request.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming you have a database connection already established

    // Check connection
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Insert test into 'tests' table
    $mysqli->query("INSERT INTO tests (referat_id) VALUES ($_POST[referat_id])");
    $testId = $mysqli->insert_id; // Get the ID of the newly inserted test

    // Loop through 5 questions
    for ($i = 1; $i <= 5; $i++) {
        $questionText = $_POST["question$i"];

        // Insert question into 'questions' table
        $mysqli->query("INSERT INTO questions (test_id, question_text) VALUES ($testId, '$questionText')");
        $questionId = $mysqli->insert_id; // Get the ID of the newly inserted question

        // Loop through 4 answers
        for ($j = 1; $j <= 4; $j++) {
            $answerText = $_POST["answer$i$j"];
            $isCorrect = isset($_POST["correct_answer$i"]) && $_POST["correct_answer$i"] == $j ? 1 : 0;

            // Insert answer into 'answers' table
            $mysqli->query("INSERT INTO answers (question_id, answer_text, is_correct) VALUES ($questionId, '$answerText', $isCorrect)");
        }
    }

    // Close the database connection
    $mysqli->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?rand<?php echo rand(1, 90); ?>">
    <title>Document</title>
  
</head>
<body>
    <?php require_once("header.php"); ?>
    <form method="post" action="process_test.php">
    <input type="hidden" name="referat_id" value="<?php echo $_GET['referat_id']; ?>">
    <!-- Loop through 5 questions -->
    <?php for ($i = 1; $i <= 5; $i++) : ?>
        <label for="question<?php echo $i; ?>">Question <?php echo $i; ?>:</label>
        <input type="text" name="question<?php echo $i; ?>" required>
        
        <!-- Loop through 4 answers for each question -->
        <?php for ($j = 1; $j <= 4; $j++) : ?>
            <label for="answer<?php echo $i . $j; ?>">Answer <?php echo $j; ?>:</label>
            <input type="text" name="answer<?php echo $i . $j; ?>" required>
            <input type="radio" name="correct_answer<?php echo $i; ?>" value="<?php echo $j; ?>">
        <?php endfor; ?>
    <?php endfor; ?>

    <button type="submit" name="add_test">Add Test</button>
</form>
    
</body>
</html>