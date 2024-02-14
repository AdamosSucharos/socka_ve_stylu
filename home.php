<?php
session_start();

$mysqli = require __DIR__ . "/connect.php";

$categories = [];
$subcategories = [];

$mysqli->select_db("kubica_soc");

$referatyData = [];


$limit = 12; 
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;


$orderBy = isset($_GET['order_by']) ? $_GET['order_by'] : 'referaty.created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

$filterCategory = '';
$searchQuery = '';


if(isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
}


if (isset($_GET["category_id"]) && $_GET["category_id"] !== '') {
    $filterCategory = "AND (category.category_id = ".$_GET["category_id"]." OR category.parent_category_id = ".$_GET["category_id"].")";
} else {
    $filterCategory = "";
}



$totalResult = $mysqli->query("SELECT COUNT(*) as total_rows 
                                FROM referaty 
                                JOIN category ON referaty.category_id = category.category_id
                                WHERE 1 $filterCategory"); 
$totalRow = $totalResult->fetch_assoc()['total_rows'];
$totalPages = ceil($totalRow / $limit);

if ($totalRow > 0) {

    $query = "SELECT referaty.referat_id, referaty.title, category.category_name, referaty.created_at, referaty.skola, user.name 
                FROM referaty
                JOIN category ON referaty.category_id = category.category_id 
                JOIN user ON referaty.user_id = user.user_id
                WHERE 1 $filterCategory
                AND (referaty.title LIKE '%$searchQuery%' OR category.category_name LIKE '%$searchQuery%' OR referaty.skola LIKE '%$searchQuery%' OR user.name LIKE '%$searchQuery%')
                ORDER BY $orderBy $order
                LIMIT $start, $limit";
    $result = $mysqli->query($query);

    while ($row = $result->fetch_assoc()) {
        $referatyData[] = $row;
    }
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
    <title>Ťaháky</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .category-list {
            padding: 0;
            margin: 0;
        }
        .category-list li {
            list-style: none;
            margin-bottom: 5px;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
        }
        .subcategory-list {
            padding-left: 20px;
            margin-top: 5px;
        }
        .hidden-subcategories {
            display: none;
        }
        .table-responsive {
            overflow-x: auto;
        }
        @media (max-width: 768px) {
            .category-list {
                margin-bottom: 15px;
            }
        }
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
    </style>
</head>
<body>
<?php require_once("header.php"); ?>
<br>
<div class="container">
    <div class="row">
        <div class="col-md-3">
            <ul class="category-list">
                <?php foreach ($categories as $category): ?>
                    <li class="category" data-category-id="<?php echo $category['category_id']; ?>">
                        <a href="#subcategories_<?php echo $category['category_id']; ?>" class="category-link">
                            <?php echo $category['category_name']; ?>
                        </a>
                        <?php if (isset($subcategories[$category['category_id']])): ?>
                            <ul id="subcategories_<?php echo $category['category_id']; ?>" class="subcategory-list hidden-subcategories">
                                <?php foreach ($subcategories[$category['category_id']] as $subcategory): ?>
                                    <li>
                                        <a href="?category_id=<?php echo $subcategory['category_id']; ?>&search=<?php echo urlencode($searchQuery); ?>">
                                            <?php echo $subcategory['category_name']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-9">
            <?php if ($totalRow > 0): ?>
                <div class="mb-3">
                    <form class="d-flex" method="GET" action="">
                        <input class="form-control me-2" type="search" placeholder="Hľadať" aria-label="Search" id="searchInput" name="search">
                        <button class="btn btn-outline-primary custom-search-button" type="submit">Hľadať</button>
                        <input type="hidden" name="category_id" value="<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>">
                    </form>
                </div>
                <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>

                        <th><a href="?order_by=title&order=<?php echo $order == 'DESC' ? 'ASC' : 'DESC'; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>">Názov</a></th>
                            <th><a href="?order_by=category_name&order=<?php echo $order == 'DESC' ? 'ASC' : 'DESC'; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>">Kategória</a></th>
                            <th><a href="?order_by=created_at&order=<?php echo $order == 'DESC' ? 'ASC' : 'DESC'; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>">Dátum vydania</a></th>
                            <th><a href="?order_by=skola&order=<?php echo $order == 'DESC' ? 'ASC' : 'DESC'; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>">Škola</a></th>
                            <th><a href="?order_by=name&order=<?php echo $order == 'DESC' ? 'ASC' : 'DESC'; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>">Autor</a></th>
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

                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-end">
                        <?php if($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page-1; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>">Predošlá</a></li>
                        <?php endif; ?>
                        <?php for($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php if($i == $page) echo 'active'; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <?php if($page < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page+1; ?>&category_id=<?php echo isset($_GET['category_id']) ? $_GET['category_id'] : ''; ?>&search=<?php echo urlencode($searchQuery); ?>">Ďalšia</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php else: ?>
                <div class="alert alert-info" role="alert">
                    No data available.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<footer class="text-center py-3">
    <p class="mb-0">© <?php echo date("Y"); ?> Referátový sklad. All rights reserved.</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {

        var categoryLinks = document.querySelectorAll('.category-link');
        categoryLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var categoryId = link.getAttribute('href').split('_')[1];
                var subcategoryList = document.getElementById('subcategories_' + categoryId);
                subcategoryList.classList.toggle('hidden-subcategories');
            });
        });


        var searchInput = document.getElementById('searchInput');
        searchInput.value = '<?php echo $searchQuery; ?>';
    });
</script>
</body>
</html>
