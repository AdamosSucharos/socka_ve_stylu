<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="header.css?rand<?php echo rand (1,90);?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


<header>

    
    <nav>
        <ul>
            <li><a href="home.php">Domov</a></li>
            <li><a href="info.php">O nás</a></li>
            <li><a href="kontakt.php">Kontakt</a></li>
        </ul>
    </nav>
    
    <form method="GET">
    <input type="text" name="search" placeholder="Hľadaj .. ">
    <input type="submit" value="Search">
    </form>

    
<?php


        $mysqli = require __DIR__ . "/connect.php";


        if (isset($_SESSION["id"])) {
            $sql = "SELECT * FROM user WHERE user_id = {$_SESSION["id"]}";
            $result = $mysqli->query($sql);

            $user = $result->fetch_assoc();

            if (isset($user["id"]) && $user["id"] == 3) {
                $admin = true;
            } else {
                $admin = false;
            }
        }
        
        if (isset($_GET["search"])) {
            $search = $_GET['search'];
        
            $result = $mysqli->query("SELECT referaty.referat_id, referaty.title, category.category_name, referaty.created_at, referaty.skola, user.name 
                            FROM referaty 
                            JOIN category ON referaty.category_id = category.category_id 
                            JOIN user ON referaty.user_id = user.user_id
                            WHERE referaty.title LIKE '%$search%'");
            if ($result->num_rows > 0) {
                $referatyData = [];
            while ($row = $result->fetch_assoc()) {
                    $referatyData[] = $row;
                    }
            } else {
                echo "<p>Žiadne výsledky.</p>";
            }
        } 

        

   if (isset($user) || isset($admin)): ?>

       <a id="user-icon" href="profile.php"><i class="fas fa-user"></i></a>
       
        <div class="button-container">
            <button class="logout-button"><a href="logout.php">Odhlásiť</a></button>
            <button class="add-button"><a href="referaty.php">Pridaj referáty</a></button>
        </div>
    <?php else: ?>
        <div class="button-container">
            <button class="register-button"><a href="register.php">Register</a></button>
            <button class="login-button"><a href="login.php">Prihlásiť sa</a></button>
        </div>
    <?php endif;

    ?>
</header>