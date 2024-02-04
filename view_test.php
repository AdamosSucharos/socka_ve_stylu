<?php
session_start();

$mysqli = require __DIR__ . "/connect.php";

$categories = [];
$subcategories = [];

if (isset($_GET['referat_id'])) {
    $referat_id = $_GET['referat_id'];

    // Fetch paper details
    $sql = "SELECT referaty.*, category.category_name 
            FROM referaty 
            JOIN category ON referaty.category_id = category.category_id 
            WHERE referaty.referat_id = $referat_id";
    $result = $mysqli->query($sql);

    if ($result && $result->num_rows > 0) {
        $paperDetails = $result->fetch_assoc();
    } else {
        echo "Paper not found.";
        exit;
    }

    // Fetch associated test details
    $testDetails = [];
    $testSql = "SELECT * FROM tests WHERE referat_id = $referat_id";
    $testResult = $mysqli->query($testSql);

    if ($testResult && $testResult->num_rows > 0) {
        $testDetails = $testResult->fetch_assoc();
    } else {
        echo "Test not found.";
        exit;
    }

    // Fetch questions and answers for the test
    $questions = [];
    $answers = [];
    $questionsSql = "SELECT * FROM questions WHERE test_id = {$testDetails['test_id']}";
    $questionsResult = $mysqli->query($questionsSql);

    if ($questionsResult && $questionsResult->num_rows > 0) {
        while ($row = $questionsResult->fetch_assoc()) {
            $questions[] = $row;

            // Fetch answers for each question
            $answersSql = "SELECT * FROM answers WHERE question_id = {$row['question_id']}";
            $answersResult = $mysqli->query($answersSql);

            if ($answersResult && $answersResult->num_rows > 0) {
                while ($answerRow = $answersResult->fetch_assoc()) {
                    $answers[$row['question_id']][] = $answerRow;
                }
            }
        }
    }
} else {
    echo "Invalid request.";
    exit;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="view_test.css?rand<?php echo rand(1, 90); ?>">
    <title>View Test</title>
</head>
<body>

<?php require_once("header.php"); ?>

<div class="container">
    <h1>Test Details for Paper: <?php echo $paperDetails['title']; ?></h1>
    <p>Kategória: <?php echo $paperDetails['category_name']; ?></p>
    <p>Test ID: <?php echo $testDetails['test_id']; ?></p>

    <?php if (!empty($questions)): ?>
        <form method="post" action="process_test_submission.php">
            <input type="hidden" name="referat_id" value="<?php echo $referat_id; ?>">
            <h2>Test Questions:</h2>
            <?php foreach ($questions as $question): ?>
                <p>Question <?php echo $question['question_id']; ?>: <?php echo $question['question_text']; ?></p>
                <?php if (isset($answers[$question['question_id']])): ?>
                    <?php foreach ($answers[$question['question_id']] as $answer): ?>
                        <label>
                            <input type="radio" name="answer[<?php echo $question['question_id']; ?>]" value="<?php echo $answer['answer_id']; ?>">
                            <?php echo $answer['answer_text']; ?>
                        </label><br>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
            <button type="submit">Submit Test</button>
        </form>
    <?php else: ?>
        <p>No questions found for this test.</p>
    <?php endif; ?>
</div>

</body>
<?php require_once("footer.php"); ?>

</html>
