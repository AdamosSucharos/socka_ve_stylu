<?php

$is_invalid = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
    
        $mysqli = require __DIR__ . "/connect.php";
        
        $email = $mysqli->real_escape_string($_POST["email"]);
        
        $sql = "SELECT * FROM user WHERE email = '$email'";
        $result = $mysqli->query($sql);
        
        $user = $result->fetch_assoc();
        
        if ($user) {
            
            // Check if the user is banned
            if ($user["is_banned"] == 1) {
                $is_invalid = true;
                $ban_message = "Tvoj účet je zabanovaný.";
            } else {
                if (password_verify($_POST["password"], $user["password_hash"])) {
                    
                    session_start();
                    session_regenerate_id();
                    
                    $_SESSION["id"] = $user["user_id"];
                    
                    header("Location: home.php");
                    exit;
                }
                $is_invalid = true;
            }
        } else {
            $is_invalid = true;
        }
        
    } else {
        $is_invalid = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="login.css?rand<?php echo rand (1,90);?>">
</head>
<body>

<div class="container">

    <h2>Login</h2>
    
    <?php if ($is_invalid): ?>
        <em><?= isset($ban_message) ? $ban_message : "Invalid login" ?></em>
    <?php endif; ?>
    
    <form method="post">
        <label for="email">email</label>
        <input type="email" name="email" id="email"
               value="<?= htmlspecialchars($_POST["email"] ?? "") ?>">
        
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        
        <button>Log in</button>
    </form>
</div>
</body>
</html>
