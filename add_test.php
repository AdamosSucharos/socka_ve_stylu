<?php
session_start();
$mysqli = require __DIR__ . "/connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['referat_id'])) {
    $referat_id = $_POST['referat_id'];


    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];

        $check_author_sql = "SELECT * FROM referaty WHERE referat_id = $referat_id AND user_id = $user_id";
        $check_author_result = $mysqli->query($check_author_sql);

        if ($check_author_result && $check_author_result->num_rows > 0) {
            
          
            $mysqli->query("INSERT INTO tests (referat_id) VALUES ($referat_id)");
            $testId = $mysqli->insert_id; 


            for ($i = 1; $i <= 5; $i++) {
                $questionText = $_POST["question$i"];


                $mysqli->query("INSERT INTO questions (test_id, question_text) VALUES ($testId, '$questionText')");
                $questionId = $mysqli->insert_id; 


                for ($j = 1; $j <= 4; $j++) {
                    $answerText = $_POST["answer$i$j"];
                    $isCorrect = isset($_POST["correct_answer$i"]) && $_POST["correct_answer$i"] == $j ? 1 : 0;


                    $mysqli->query("INSERT INTO answers (question_id, answer_text, is_correct) VALUES ($questionId, '$answerText', $isCorrect)");
                }
            }

            echo "<script>alert('Test bol úspešne pridaný!'); window.location.href='view_paper.php?referat_id=$referat_id';</script>";
            exit; 
        } else {
            echo "Nemáš práva na pridanie testu pre tento referát!.";;
        }
    } else {
        echo "Musíš sa prihlásiť.";
    }
}

if (isset($_GET['referat_id'])) {
    $referat_id = $_GET['referat_id'];


    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];

        $check_author_sql = "SELECT * FROM referaty WHERE referat_id = $referat_id AND user_id = $user_id";
        $check_author_result = $mysqli->query($check_author_sql);

        if ($check_author_result && $check_author_result->num_rows > 0) {

          $check_test_sql = "SELECT * FROM tests WHERE referat_id = $referat_id";
            $check_test_result = $mysqli->query($check_test_sql);

            if ($check_test_result && $check_test_result->num_rows > 0) {
                echo "Tento referát už obsahuje test!";
            } else {


              ?>

                <!DOCTYPE html>
                <html lang="sk">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
                    <title>Add Test</title>
                    <style>
                        body {
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            background-color: #f8f9fa;
                        }
                        .container {
                            padding-top: 50px;
                            padding-bottom: 80px;
                        }
                        .card {
                            border-radius: 15px;
                            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                        }
                        .card-header {
                            background-color: #007bff;
                            color: #fff;
                            border-radius: 15px 15px 0 0;
                        }
                        .card-body {
                            padding: 30px;
                        }
                        .form-label {
                            font-weight: bold;
                        }
                        .form-control {
                            border-radius: 10px;
                            border: 1px solid #ced4da;
                            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
                        }
                        .form-control:focus {
                            border-color: #007bff;
                            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                        }
                        .btn-primary {
                            border-radius: 10px;
                        }
                        footer {
                            position: fixed;
                            bottom: 0;
                            width: 100%;
                            height: 60px;
                            background-color: #f8f9fa;
                            text-align: center;
                            line-height: 60px;
                            font-size: 14px;
                            color: #6c757d;
                            border-top: 1px solid #dee2e6;
                        }
                    </style>
                </head>
                <body>
                <?php require_once("header.php"); ?>
                <div class="container">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="text-center mb-0">Pridaj test</h2>
                        </div>
                        <div class="card-body">
                            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                <input type="hidden" name="referat_id" value="<?php echo isset($_GET['referat_id']) ? $_GET['referat_id'] : ''; ?>">
                                <?php for ($i = 1; $i <= 5; $i++) : ?>
                                    <div class="mb-4">
                                        <h4 class="mb-3">Otázka <?php echo $i; ?></h4>
                                        <div class="mb-3">
                                            <label for="question<?php echo $i; ?>" class="form-label">Otázka:</label>
                                            <input type="text" name="question<?php echo $i; ?>" required class="form-control">
                                        </div>
                                        <div class="row answer-options">
                                            <?php for ($j = 1; $j <= 4; $j++) : ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-check">
                                                        <input type="text" name="answer<?php echo $i . $j; ?>" required class="form-control">
                                                        <input type="radio" name="correct_answer<?php echo $i; ?>" value="<?php echo $j; ?>" required class="form-check-input">
                                                        <label class="form-check-label" for="answer<?php echo $i . $j; ?>">Odpoveď <?php echo $j; ?></label>
                                                    </div>
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                                <button type="submit" name="add_test" class="btn btn-primary btn-block">Pridaj test</button>
                            </form>
                        </div>
                    </div>
                </div>

                <footer>
                    <div class="container">
                        © <?php echo date("Y"); ?> Referátový sklad. All rights reserved.
                    </div>
                </footer>
                </body>
                </html>

                <?php
            }
        } else {
            echo "Nemáš práva na pridanie testu pre tento referát!.";
        }
    } else {
        echo "Musíš sa prihlásiť!";
    }
} else {
    header("Location: login.php");
}
?>
