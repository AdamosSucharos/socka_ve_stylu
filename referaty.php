<?php
session_start();

$errReferat = false; 

if (isset($_SESSION["id"])) {
    $mysqli = require __DIR__ . "/connect.php";
    
    $sql = "SELECT * FROM user WHERE user_id = {$_SESSION["id"]}";
    $resultUser = $mysqli->query($sql);

    $userDetails = $resultUser->fetch_assoc();
    $user_id = $userDetails['user_id'];
} else {
    header("Location: login.php");
}

$clear = [];


$sqlSelect = "SELECT category_id, category_name FROM category WHERE parent_category_id IS NOT NULL";
$resultCategory = $mysqli->query($sqlSelect);

if ($resultCategory->num_rows > 0) {
    $categories = $resultCategory->fetch_all(MYSQLI_ASSOC);
}

if (isset($_POST['pridat'])) {
    $category_id = $_POST['category_id'];
    $title = $_POST['title'];
    $skola = $_POST['skola'];
    $content = $_POST['content'];

    if ($category_id == "" || $user_id == "" || $title == "" || $skola == "" || $content == "") {
        $err = true;
    } else if ($category_id == "-1") {
        $errReferat = true;
    } else {
        $sql = "INSERT INTO referaty (user_id, category_id, title, skola, content) VALUES (?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("iisss", $user_id, $category_id, $title, $skola, $content);
        
        $isInserted = $stmt->execute();

        if ($isInserted) {
            echo "Referat added successfully.";
            header("Location: home.php");
            exit();
        } else {
            $clear [] = "Nastala chyba pri vkladaní referátu.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pridávanie referátu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://cdn.tiny.cloud/1/t44apa8tzckyi7zfwalq5x005xqrcqalbiz64jcgwrh854ps/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            height: 300,
            plugins: [
                'advlist autolink lists link charmap preview anchor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime media table paste code help wordcount',
                'fontsize'
            ],
            toolbar: 'undo redo | formatselect | ' +
                'bold italic underline | ' +
                'alignleft aligncenter alignright alignjustify | ' +
                'bullist numlist outdent indent | ' +
                'removeformat | fontsize | help',
            content_style: 'body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }'
        });
    </script>
    <style>
        body{
            background-color: gray;
        }
        .card-body{
            background-color: f5f5f5;
        }
        
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h1 class="card-title mb-0">Pridávanie referátu</h1>
                </div>
                <div class="card-body">
                    <?php if(!empty($clear)) { echo $clear; } ?>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                        <div class="mb-3">
                            <label for="title" class="form-label">Názov referátu</label>
                            <input type="text" id="title" name="title" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Kategória</label>
                            <select name="category_id" id="category_name" class="form-select">
                                <option value="-1"></option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id'] ?>">
                                        <?php echo $cat['category_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if($errReferat): ?>
                                <div class="text-danger">Vyberte kategóriu</div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="skola" class="form-label">Vyber školu</label>
                            <select id="skola" name="skola" class="form-select">
                                <option value="ZŠ">Základná škola</option>
                                <option value="SŠ">Stredná škola</option>
                                <option value="VŠ">Vysoká škola</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Obsah referátu</label>
                            <textarea id="content" name="content" class="form-control"></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" value="Pridať" name="pridat" class="btn btn-primary">Pridať</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
