<?php
session_start();

$mysqli = require __DIR__ . "/connect.php";

$categories = [];
$subcategories = [];
$errors = [];
$score = null;

if (isset($_GET['referat_id'])) {
    $referat_id = $_GET['referat_id'];

    $sql = "SELECT referaty.*, category.category_name 
            FROM referaty 
            JOIN category ON referaty.category_id = category.category_id 
            WHERE referaty.referat_id = $referat_id";
    $result = $mysqli->query($sql);

    if ($result && $result->num_rows > 0) {
        $paperDetails = $result->fetch_assoc();

        $testDetails = [];
        $testSql = "SELECT * FROM tests WHERE referat_id = $referat_id";
        $testResult = $mysqli->query($testSql);

        if ($testResult && $testResult->num_rows > 0) {
            $testDetails = $testResult->fetch_assoc();

            $questions = [];
            $answers = [];
            $questionsSql = "SELECT * FROM questions WHERE test_id = {$testDetails['test_id']}";
            $questionsResult = $mysqli->query($questionsSql);

            if ($questionsResult && $questionsResult->num_rows > 0) {
                while ($row = $questionsResult->fetch_assoc()) {
                    $questions[] = $row;

                    $answersSql = "SELECT * FROM answers WHERE question_id = {$row['question_id']}";
                    $answersResult = $mysqli->query($answersSql);

                    if ($answersResult && $answersResult->num_rows > 0) {
                        while ($answerRow = $answersResult->fetch_assoc()) {
                            $answerRow['is_selected'] = false;
                            $answers[$row['question_id']][] = $answerRow;
                        }
                    }
                }
            }
        } else {
            $errors[] = "Test neexistuje.";
        }
    } else {
        $errors[] = "Referát neexistuje.";
    }
} else {
    $errors[] = "Invalid request.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $totalQuestions = count($_POST['answer']);
    $correctAnswers = 0;

    foreach ($_POST['answer'] as $questionId => $selectedAnswerId) {
        $correctAnswerSql = "SELECT is_correct FROM answers WHERE question_id = $questionId AND answer_id = $selectedAnswerId AND is_correct = 1";
        $correctAnswerResult = $mysqli->query($correctAnswerSql);

        if ($correctAnswerResult && $correctAnswerResult->num_rows > 0) {
            $correctAnswers++;
        }
    }

    $score = ($correctAnswers / $totalQuestions) * 100;

    foreach ($answers as $questionId => &$answerOptions) {
        foreach ($answerOptions as &$answer) {
            $answer['is_selected'] = isset($_POST['answer'][$questionId]) && $_POST['answer'][$questionId] == $answer['answer_id'];
            $answer['is_correct'] = $answer['is_selected'] && $answer['is_correct'];
        }
    }

    $user_id = $_SESSION['id'];
    $check_submission_sql = "SELECT * FROM user_submissions WHERE user_id = $user_id AND test_id = {$testDetails['test_id']}";
    $check_submission_result = $mysqli->query($check_submission_sql);

    if ($check_submission_result && $check_submission_result->num_rows == 0) {
        $insert_submission_sql = "INSERT INTO user_submissions (user_id, test_id, score) VALUES ($user_id, {$testDetails['test_id']}, $score)";
        $mysqli->query($insert_submission_sql);
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test</title>
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
        .card {
            padding-top: 20px;
            margin-bottom: 80px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .breadcrumb {
            background-color: transparent;
        }
        .breadcrumb-item a, .breadcrumb-item.active {
            color: #007bff;
            font-weight: bold;
        }
        .breadcrumb-item.active {
            color: #000;
        }
        .error-message {
            color: #dc3545;
        }
        .correct-answer {
            color: green;
        }
        .wrong-answer {
            color: red;
        }
        .form-check-input:checked + .form-check-label::before {
            background-color: #007bff;
            border-color: #007bff;
        }
        .form-check-input:checked + .form-check-label::after {
            background-color: #fff;
        }
    </style>
</head>
<body>

<?php require_once("header.php"); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger error-message" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endforeach; ?>

            <div class="card">
                <div class="card-body">
                    <?php if (isset($score)): ?>
                        <div class="alert alert-success" role="alert">
                            Tvoje skóre je: <?php echo round($score, 2); ?>%
                        </div>
                    <?php endif; ?>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="home.php">Domov</a></li>
                            <li class="breadcrumb-item"><a href="view_paper.php?referat_id=<?php echo $paperDetails['referat_id']; ?>"><?php echo $paperDetails["title"];?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo isset($paperDetails['title']) ? $paperDetails['title'] : ''; ?></li>
                        </ol>
                    </nav>
                    <h1 class="card-title"><?php echo $paperDetails['title']; ?></h1>
                    <p class="card-text">Kategória: <?php echo $paperDetails['category_name']; ?></p>

                    <?php if (!empty($questions)): ?>
                        <form method="post">
                            <input type="hidden" name="referat_id" value="<?php echo $referat_id; ?>">
                            <?php foreach ($questions as $question): ?>
                                <p><?php echo $question['question_text']; ?></p>
                                <?php if (isset($answers[$question['question_id']])): ?>
                                    <?php foreach ($answers[$question['question_id']] as $answer): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="answer[<?php echo $question['question_id']; ?>]" value="<?php echo $answer['answer_id']; ?>" id="answer_<?php echo $answer['answer_id']; ?>" <?php echo $answer['is_selected'] ? 'checked' : ''; ?> required>
                                            <label class="form-check-label <?php echo $answer['is_selected'] && $answer['is_correct'] ? 'correct-answer' : ($answer['is_selected'] ? 'wrong-answer' : ''); ?>" for="answer_<?php echo $answer['answer_id']; ?>">
                                                <?php echo $answer['answer_text']; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary mt-3">Skontrolovať</button>
                        </form>
                    <?php else: ?>
                        <p>Test neexistuje</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="text-center py-3 fixed-bottom">
    <p class="mb-0">© <?php echo date("Y"); ?> Referátový sklad. All rights reserved.</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
