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
    
        $userHasPapers = $mysqli->query("SELECT COUNT(*) FROM referaty WHERE user_id = $userIdToDelete")->fetch_assoc()['COUNT(*)'];
    
        if ($userHasPapers > 0) {
            $mysqli->query("UPDATE referaty SET user_id = 3 WHERE user_id = $userIdToDelete");
        }
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
    <link rel="stylesheet" href="profile.css?rand<?php echo rand(1, 90); ?>";>

    <title>User Profile</title>

</head>
<body>
<?php require_once("header.php"); ?>

<?php if ($isAdmin): ?>
    <h3>Admin Panel</h3>
    <div class="content">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Meno <form method="get" action="">
                            <label for="name"></label>
                            <input type="text" name="name" id="name" placeholder="Meno">
                            <button type="submit">Hľadaj</button>
                        </form>
                </th>
                <th>Akcie</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usersData as $userData): 
                
                if (isset($_GET['name']) && !empty($_GET['name'])) {
                    $searchName = $_GET['name'];
                    if (stripos($userData['name'], $searchName) === false) {
                        continue; 
                    }
                }
                ?>
                <tr>
                    <td><?php echo $userData['user_id']; ?></td>
                    <td><?php echo $userData['name'] . " " . $userData['email'] ;?></td>
                    <td>               

                        <form method="post">
                            <button type="submit" name="admin_action" value="delete_user">
                                Delete User
                            </button>
                            <input type="hidden" name="user_id_to_delete" value="<?php echo $userData['user_id']; ?>">
                        </form>
                        <form method="post">
                            <button type="submit" name="admin_action" value="ban_user">
                                <?php echo ($user['is_banned'] == 0) ? 'Ban' : 'Unban'; ?>
                            </button>
                            <input type="hidden" name="user_id_to_ban" value="<?php echo $userData['user_id']; ?>">
                        </form>

                        <form method="post">
                            <input type="password" name="new_password" placeholder="Nové heslo" required>
                            <button type="submit" name="admin_action" value="change_password">
                                Zmeň heslo
                            </button>
                            <input type="hidden" name="user_id_to_change" value="<?php echo $userData['user_id']; ?>">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pridanie">
    <form method="post">
        <input type="text" name="name" placeholder="Prezývka" required>
        <input type="email" name="email" id="email" placeholder="E-mail" required>
        <input type="password" name="password" placeholder="Heslo" required>
        <button type="submit" name="admin_action" value="add_user">Pridaj používateľa</button>
    </form>
    </div>
</div>
<?php endif; ?>

<h3>Tvoje referáty</h3>
<table>
    <thead>
        <tr>
            <th>Názov</th>
            <th>Kategória</th>
            <th>Dátum vydania</th>
            <th>Škola</th>
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
                        <button type="submit" name="delete_paper" value="<?php echo $row['referat_id']; ?>">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
<?php

