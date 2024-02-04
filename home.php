<?php
session_start();

$mysqli = require __DIR__ . "/connect.php";



$categories = [];
$subcategories = [];

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


$mysqli->select_db("kubica_soc");


$referatyData = [];
if(isset($_GET["category_id"]))
{
    $kategoria = $_GET["category_id"];
        $result = $mysqli->query("SELECT referaty.referat_id, referaty.title, category.category_name, referaty.created_at, referaty.skola, user.name 
                          FROM referaty
                          JOIN category ON referaty.category_id = category.category_id 
                          JOIN user ON referaty.user_id = user.user_id
                          WHERE category.category_id = $kategoria" );
    
    if (isset($_GET["search"])) {
        $search = $_GET['search'];
    
        $result = $mysqli->query("SELECT referaty.referat_id, referaty.title, category.category_name, referaty.created_at, referaty.skola, user.name 
                        FROM referaty 
                        JOIN category ON referaty.category_id = category.category_id 
                        JOIN user ON referaty.user_id = user.user_id
                        WHERE referaty.title LIKE '%$search%' AND caregory.category_id = $kategoria ");}

}

else{
    $result = $mysqli->query("SELECT referaty.referat_id, referaty.title, category.category_name, referaty.created_at, referaty.skola, user.name 
    FROM referaty 
    JOIN category ON referaty.category_id = category.category_id 
    JOIN user ON referaty.user_id = user.user_id");
}


while ($row = $result->fetch_assoc()) {
    $referatyData[] = $row;
}


$result = $mysqli->query("SELECT * FROM category WHERE parent_category_id IS NULL");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}


$result = $mysqli->query("SELECT * FROM category WHERE parent_category_id IS NOT NULL");
while ($row = $result->fetch_assoc()) {
    $subcategories[$row['parent_category_id']][] = $row;
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?rand<?php echo rand(1, 90); ?>">
    <title>ťaháky</title>
</head>
<body>
    <?php require_once("header.php"); ?>

    <ul id="categoriesList">
        <?php foreach ($categories as $category): ?>
            <li class="category" data-category-id="<?php echo $category['category_id']; ?>">
                <?php echo $category['category_name']; ?>
                <ul class="subcategories hidden-subcategories">
                    <?php if (isset($subcategories[$category['category_id']])): ?>
                        <?php foreach ($subcategories[$category['category_id']] as $subcategory): ?>
                            <form method="GET" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <input type="hidden" name="category_id" value="<?php echo $subcategory['category_id'];?>">
                                <a href="?category_id=<?php echo urlencode($subcategory['category_id']); ?>">
                                    <?php echo $subcategory['category_name']; ?>
                                </a>
                            </form>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </li>
        <?php endforeach; ?>
    </ul>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var categoryElements = document.querySelectorAll(".category");

            categoryElements.forEach(function (categoryElement) {
                categoryElement.addEventListener("click", function () {
                                      
                    document.querySelectorAll(".subcategories").forEach(function (subcategoriesList) {
                        subcategoriesList.classList.add("hidden-subcategories");
                    });

                                  
                    var subcategoriesList = categoryElement.querySelector(".subcategories");
                    subcategoriesList.classList.remove("hidden-subcategories");
                });
            });
        });
    </script>
<?php if (isset($_GET['search'])): ?>

    <div class="tmp">
    <table>
        <thead>
            <tr>
                <th>Názov</th>
                <th>Kategória</th>
                <th>Dátum vydania</th>
                <th>Škola</th>
                <th>Autor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($referatyData as $row): ?>
                <tr>
                    <td><a href="view_paper.php?referat_id=<?php echo $row['referat_id']; ?>"><?php echo $row['title']; ?></a></td>
                    <td><?php echo $row['category_name']; ?></td>
                    <td><?php echo $row['created_at']; ?></td>
                    <td><?php echo $row['skola']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    <table>
    <thead>
        <tr>
            <th>Názov</th>
            <th>Kategória</th>
            <th>Dátum vydania</th>
            <th>Škola</th>
            <th>Autor</th>
            
        </tr>
    </thead>
    <tbody>
        <?php foreach ($referatyData as $row): ?>
            <tr>
                <td><a href="view_paper.php?referat_id=<?php echo $row['referat_id']; ?>"><?php echo $row['title']; ?></a></td>
                <td><?php echo $row['category_name']; ?></td>
                <td><?php echo $row['created_at']; ?></td>
                <td><?php echo $row['skola']; ?></td>
                <td><?php echo $row['name']; ?></td>
                
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

    
    
</body>
<?php include("footer.php");?>
</html>
