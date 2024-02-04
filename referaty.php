<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <title>Pridavanie referátu</title>
    <link rel="stylesheet" href="referaty.css?rand<?php echo rand(1, 90); ?>">
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

</head>
<body>
<?php
require_once("connect.php");
$err = 0;
$errReferat = false;
?>

<div class="container">
    <h1>Pridanie referátu</h1>
    
    <form action="process-pridanie.php" method="post">
        <label for="title">Názov</label><br>
        <input type="text" id="title" name="title">

        <label for="category_name">Kategória:</label>
        <select name="category_id" id="category_name">
            <option value="-1"></option>
            <?php
            $sqlSelect = "SELECT category_id, category_name FROM category WHERE parent_category_id IS NOT NULL";
            $resultCategory = $mysqli->query($sqlSelect);

            if ($resultCategory->num_rows > 0) {
                $categories = $resultCategory->fetch_all(MYSQLI_ASSOC);
            }

            foreach ($categories as $cat) {
                ?>
                <option value="<?php echo $cat['category_id'] ?>">
                    <?php echo $cat['category_name'] ?>
                </option>
                <?php
            }
            ?>
        </select>

        <label for="skola">Vyber školu:</label>
        <select id="skola" name="skola">
            <option value="ZŠ">Základná škola</option>
            <option value="SŠ">Stredná škola</option>
            <option value="VŠ">Vysoká škola</option>
        </select>

        <label for="content">Obsah:</label>
        <textarea id="content" name="content"></textarea>

        <br>
        <input type="submit" value="Pridať" name="pridat">
    </form>
</div>

</body>
</html>
