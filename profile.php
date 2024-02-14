<?php
session_start();


if (!isset($_SESSION['id'])) {
    header("Location: login.php"); 
    exit;
}

$mysqli = require __DIR__ . "/connect.php";
$userId = $_SESSION['id'];

$userSql = sprintf("SELECT * FROM user WHERE user_id = %d", $userId);
$userResult = $mysqli->query($userSql);
$user = $userResult->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_paper"])) {
    $paperIdToDelete = $_POST["delete_paper"];
    

    $deleteUserSubmissionsSql = sprintf("DELETE FROM user_submissions WHERE test_id IN (SELECT test_id FROM tests WHERE referat_id = %d)", $paperIdToDelete);
    $mysqli->query($deleteUserSubmissionsSql);


    $deleteAnswersSql = sprintf("DELETE FROM answers WHERE question_id IN (SELECT question_id FROM questions WHERE test_id IN (SELECT test_id FROM tests WHERE referat_id = %d))", $paperIdToDelete);
    $mysqli->query($deleteAnswersSql);


    $deleteQuestionsSql = sprintf("DELETE FROM questions WHERE test_id IN (SELECT test_id FROM tests WHERE referat_id = %d)", $paperIdToDelete);
    $mysqli->query($deleteQuestionsSql);


    $deleteTestsSql = sprintf("DELETE FROM tests WHERE referat_id = %d", $paperIdToDelete);
    $mysqli->query($deleteTestsSql);


    $deletePaperSql = sprintf("DELETE FROM referaty WHERE referat_id = %d AND user_id = %d", $paperIdToDelete, $userId);
    $mysqli->query($deletePaperSql);

    header("Location: profile.php");
    exit;
}
$referatyData = [];
$userId = $_SESSION['id'];

$result = $mysqli->query("SELECT referaty.referat_id, referaty.title, category.category_name, referaty.created_at, referaty.skola, user.name  
                          FROM referaty 
                          JOIN category ON referaty.category_id = category.category_id 
                          JOIN user ON referaty.user_id = user.user_id
                          WHERE referaty.user_id = $userId");

while ($row = $result->fetch_assoc()) {
    $referatyData[] = $row;
}

$isAdmin = ($user['user_id'] == 3);

if ($isAdmin && isset($_POST['admin_action'])) {
    $adminAction = $_POST['admin_action'];

    if ($adminAction == 'add_user' && isset($_POST['name'], $_POST['email'],$_POST['password'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $mysqli->query("INSERT INTO user (name, email, password_hash) VALUES ('$name', '$email', '$password_hash' )");
    }

    elseif ($adminAction == 'delete_user' && isset($_POST['user_id_to_delete'])) {
        $userIdToDelete = $_POST['user_id_to_delete'];
        

        $mysqli->query("DELETE FROM user_submissions WHERE user_id = $userIdToDelete");
    

        $papersResult = $mysqli->query("SELECT referat_id FROM referaty WHERE user_id = $userIdToDelete");
    

        while ($row = $papersResult->fetch_assoc()) {
            $referatId = $row['referat_id'];
            

            $testsResult = $mysqli->query("SELECT test_id FROM tests WHERE referat_id = $referatId");
    

            while ($testRow = $testsResult->fetch_assoc()) {
                $testId = $testRow['test_id'];
                

                $mysqli->query("DELETE FROM answers WHERE question_id IN (SELECT question_id FROM questions WHERE test_id = $testId)");
    

                $mysqli->query("DELETE FROM questions WHERE test_id = $testId");
            }
    

            $mysqli->query("DELETE FROM tests WHERE referat_id = $referatId");
        }
    

        $mysqli->query("DELETE FROM referaty WHERE user_id = $userIdToDelete");
    

        $mysqli->query("DELETE FROM user WHERE user_id = $userIdToDelete");
    }
    elseif ($adminAction == 'ban_user' && isset($_POST['user_id_to_ban'])) {
        $userIdToBan = $_POST['user_id_to_ban'];
    
        $banStatus = $mysqli->query("SELECT is_banned FROM user WHERE user_id = $userIdToBan")->fetch_assoc()['is_banned'];
    
        $newBanStatus = ($banStatus == 0) ? 1 : 0;
    
        $mysqli->query("UPDATE user SET is_banned = $newBanStatus WHERE user_id = $userIdToBan");
    }

    elseif ($adminAction == 'change_password' && isset($_POST['user_id_to_change'], $_POST['new_password'])) {
        $userIdToChange = $_POST['user_id_to_change'];
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $mysqli->query("UPDATE user SET password_hash = '$newPassword' WHERE user_id = $userIdToChange");
    }
}

if ($isAdmin) {
    $usersResult = $mysqli->query("SELECT * FROM user");
    $usersData = $usersResult->fetch_all(MYSQLI_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
            margin-bottom: 50px;
        }
        h3 {
            color: #007bff;
        }
        .table {
            border-radius: 10px;
        }
        .btn {
            border-radius: 20px;
        }
        footer {
            background-color: lightgrey;
            color: fblackff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
        }
    </style>
</head>
<body>
<?php require_once("header.php"); ?>
<div class="container">
    <?php if ($isAdmin): ?>
        <h3 class="mb-4">Admin Panel</h3>
        <div class="content">
            <div class="table-responsive">
                <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Meno</th>
                                <th>E-mail</th>
                                <th>Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usersData as $userData): ?>
                                <tr>
                                    <td><?php echo $userData['user_id']; ?></td>
                                    <td><?php echo $userData['name']; ?></td>
                                    <td><?php echo $userData['email']; ?></td>
                                    <td>               
                                        <form method="post">
                                            <button type="submit" name="admin_action" value="delete_user" class="btn btn-danger btn-sm">Delete User</button>
                                            <input type="hidden" name="user_id_to_delete" value="<?php echo $userData['user_id']; ?>">
                                            <button type="submit" name="admin_action" value="ban_user" class="btn btn-warning btn-sm"><?php echo $userData["is_banned"] == 0 ? "Ban" : "Unban"; ?></button>
                                            <input type="hidden" name="user_id_to_ban" value="<?php echo $userData['user_id']; ?>">
                                        </form>
                                        <form method="post">
                                            <input type="password" name="new_password" placeholder="Nové heslo" required class="form-control form-control-sm">
                                            <button type="submit" name="admin_action" value="change_password" class="btn btn-primary btn-sm">Zmeň heslo</button>
                                            <input type="hidden" name="user_id_to_change" value="<?php echo $userData['user_id']; ?>">
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="pridanie mt-2"> 
                                <h4>Pridaj nového používateľa</h4>
                                <form method="post">
                                    <div class="mb-3">
                                        <input type="text" name="name" placeholder="Prezývka" required class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <input type="email" name="email" id="email" placeholder="E-mail" required class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <input type="password" name="password" placeholder="Heslo" required class="form-control">
                                    </div>
                                    <button type="submit" name="admin_action" value="add_user" class="btn btn-success" style="background-color: #007bff;">Pridaj používateľa</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="wrapper mt-5">
        <div class="papers">
            <h3 class="mb-4">Tvoje referáty</h3>
            <div class="table-responsive">
                <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Názov</th>
                                <th>Kategória</th>
                                <th>Dátum vydania</th>
                                <th>Škola</th>
                                <th>Akcie</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referatyData as $row): ?>
                                <tr>
                                    <td><a href="view_paper.php?referat_id=<?php echo $row['referat_id']; ?>"><?php echo $row['title']; ?></a></td>
                                    <td><?php echo $row['category_name']; ?></td>
                                    <td><?php echo $row['created_at']; ?></td>
                                    <td><?php echo $row['skola']; ?></td>
                                    <td>
                                        <form method="post">
                                            <button type="submit" name="delete_paper" value="<?php echo $row['referat_id']; ?>" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="tests">
                <h3 class="mb-4">Tvoje testy</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Názov referátu</th>
                                <th>Tvoje skóre</th>
                                <th>Rank</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php

$testSubmissionsSql = "SELECT tests.test_id, tests.referat_id, referaty.title AS test_title, user_submissions.score 
                                                FROM tests 
                                                JOIN referaty ON tests.referat_id = referaty.referat_id 
                                                JOIN user_submissions ON tests.test_id = user_submissions.test_id 
                                                WHERE user_submissions.user_id = $userId";
                            $testSubmissionsResult = $mysqli->query($testSubmissionsSql);

                            if ($testSubmissionsResult && $testSubmissionsResult->num_rows > 0) {
                                while ($testSubmission = $testSubmissionsResult->fetch_assoc()) {
                                    $testId = $testSubmission['test_id'];
                                    $referatId = $testSubmission['referat_id'];
                                    $testTitle = $testSubmission['test_title'];
                                    $score = $testSubmission['score'];

                                    // Fetch rank in test scores
                                    $rankSql = "SELECT COUNT(*) AS rank FROM user_submissions 
                                            WHERE test_id = $testId AND score > (SELECT IFNULL(score, 0) FROM user_submissions WHERE user_id = $userId AND test_id = $testId)";
                                    $rankResult = $mysqli->query($rankSql);
                                    $rank = ($rankResult && $rankResult->num_rows > 0) ? $rankResult->fetch_assoc()['rank'] + 1 : 0;
                            ?>
                                    <tr>
                                        <td><?php echo $testTitle; ?></td>
                                        <td><?php echo $score !== null ? $score : "Not attempted"; ?></td>
                                        <td><?php echo $rank > 0 ? $rank : "N/A"; ?></td>
                                    </tr>
                            <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="4">No test submissions found.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php 
$mysqli->close();
?>
<footer class="text-center py-3 fixed-bottom">
    <p class="mb-0">© <?php echo date("Y"); ?> Referátový sklad. All rights reserved.</p>
</footer>

</body>
</html>
