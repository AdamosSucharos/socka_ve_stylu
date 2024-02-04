<?php
session_start();
$mysqli = require __DIR__ . "/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['referat_id'])) {
    $referat_id = $_POST['referat_id'];

    // Check if the logged-in user is the author of the paper
    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];

        $check_author_sql = "SELECT * FROM referaty WHERE referat_id = $referat_id AND user_id = $user_id";
        $check_author_result = $mysqli->query($check_author_sql);

        if ($check_author_result && $check_author_result->num_rows > 0) {
            // The logged-in user is the author of the paper
            // Insert test into 'tests' table
            $mysqli->query("INSERT INTO tests (referat_id) VALUES ($referat_id)");
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

            echo "Test added successfully!";
        } else {
            echo "You do not have permission to add a test for this paper.";
        }
    } else {
        echo "You must be logged in to add a test.";
    }
} else {
    echo "Invalid request.";
}

// Close the database connection
$mysqli->close();
?>
