<?php
session_start();

$mysqli = require __DIR__ . "/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['referat_id']) || !isset($_POST['answer'])) {
        header("Location: home.php");
        exit;
    }

    $referat_id = $_POST['referat_id'];
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

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
    // Insert user's answers into the database
    foreach ($_POST['answer'] as $question_id => $selected_answer_id) {
        // Check if the selected answer is correct
        $checkCorrectSql = "SELECT * FROM answers WHERE answer_id = $selected_answer_id AND is_correct = 1 LIMIT 1";
        $checkCorrectResult = $mysqli->query($checkCorrectSql);

        if ($checkCorrectResult && $checkCorrectResult->num_rows > 0) {
            $is_correct = 1;
        } else {
            $is_correct = 0;
        }

        // Insert the user's answer into the database
        $insertAnswerSql = "INSERT INTO user_answers (user_id, question_id, selected_answer_id, is_correct) 
                            VALUES ($user_id, $question_id, $selected_answer_id, $is_correct)";
        if (!$mysqli->query($insertAnswerSql)) {
            echo "Error: " . $mysqli->error;
        }
    }

    // Check if the user has already submitted the test
    $checkSubmissionSql = "SELECT * FROM user_submissions WHERE user_id = $user_id AND test_id = {$testDetails['test_id']}";
    $checkSubmissionResult = $mysqli->query($checkSubmissionSql);

    if ($checkSubmissionResult && $checkSubmissionResult->num_rows > 0) {
        echo "You have already submitted the test.";
        exit;
    }


    
    // Insert user's answers into the database
    foreach ($_POST['answer'] as $question_id => $selected_answer_id) {
        // Check if the selected answer is correct
        $checkCorrectSql = "SELECT * FROM answers WHERE answer_id = $selected_answer_id AND is_correct = 1 LIMIT 1";
        $checkCorrectResult = $mysqli->query($checkCorrectSql);
    
        if ($checkCorrectResult && $checkCorrectResult->num_rows > 0) {
            $is_correct = 1;
        } else {
            $is_correct = 0;
        }
    
        // Insert the user's answer into the database
        $insertAnswerSql = "INSERT INTO user_answers (user_id, question_id, selected_answer_id, is_correct) 
                            VALUES ($user_id, $question_id, $selected_answer_id, $is_correct)";
        $mysqli->query($insertAnswerSql);
    }
    
    // Insert the submission record to prevent multiple submissions
    $insertSubmissionSql = "INSERT INTO user_submissions (user_id, test_id) VALUES ($user_id, {$testDetails['test_id']})";
    $mysqli->query($insertSubmissionSql);
    
    echo "Test submitted successfully!";
} else {
    header("Location: home.php");
    exit;
}

$mysqli->close();
?>
