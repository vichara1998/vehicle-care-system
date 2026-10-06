<?php
require_once __DIR__ . '/../app/bootstrap.php';

$conn = app_db_connect();

// Initialize catalog data and validate query parameters.
$spare_parts = [];
$search_query = trim((string) ($_GET['search'] ?? ''));
$search_query = function_exists('mb_substr') ? mb_substr($search_query, 0, 100, 'UTF-8') : substr($search_query, 0, 100);
$requestedPage = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$page = $requestedPage === false ? 1 : $requestedPage;
$pageSize = 8;

$whereSql = '';
$searchTerm = '%' . $search_query . '%';
if ($search_query !== '') {
    $whereSql = ' WHERE item_name LIKE ? OR description LIKE ?';
}

$countStmt = $conn->prepare('SELECT COUNT(*) AS total FROM spare_parts' . $whereSql);
if ($search_query !== '') {
    $countStmt->bind_param('ss', $searchTerm, $searchTerm);
}
$countStmt->execute();
$totalItems = (int) $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, (int) ceil($totalItems / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;
$sql = 'SELECT spare_id, item_name, description, price, image_path, stock FROM spare_parts'
    . $whereSql
    . ' ORDER BY spare_id DESC LIMIT ? OFFSET ?';
$stmt = $conn->prepare($sql);
if ($search_query !== '') {
    $stmt->bind_param('ssii', $searchTerm, $searchTerm, $pageSize, $offset);
} else {
    $stmt->bind_param('ii', $pageSize, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $spare_parts[] = $row;
}
$stmt->close();
$conn->close();

$pageWindowStart = max(1, $page - 2);
$pageWindowEnd = min($totalPages, $page + 2);
$paginationQuery = $search_query === '' ? [] : ['search' => $search_query];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Spare Parts</title>
    <style>
        /* Existing CSS: Retained from your template */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        .menu-bar {
            background-color:rgb(255, 255, 255);
            color: #333;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 60px rgba(0, 0, 0, 0.51);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .menu-bar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }

        .menu-bar li {
            display: inline;
            margin: 0 20px;
        }

        .menu-bar a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            font-size: 18px;
            padding: 8px 12px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .menu-bar a:hover {
            background-color: rgb(62, 62, 62);
            color: #fff;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
           
        }

        .spare-part {
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px;
            padding: 10px;
            text-align: center;
            background: #fff;
            display: inline-block;
            width: 220px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
             margin: 0px 0px 25px 30px;
        }

        .spare-part:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .spare-part h3 {
            font-size: 1.2em;
            color: #333;
            
        }

        .spare-part p {
            color: #666;
        }

        .spare-part .price {
            font-weight: bold;
            color: #e63946;
        }

        .status {
            font-weight: bold;
            padding: 5px;
            border-radius: 3px;
        }

        .in-stock {
            color: #28a745;
        }

        .out-of-stock {
            color: #dc3545;
        }

        .view-cart-button,
        .view-orders-button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            align-items: end;
            margin: 60px 10px 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* Increased top margin for more space below */
        }

       

        .menu-bar a.button-link {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border-radius: 15px;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.11);
            
        }

        .menu-bar a.button-link:hover {
            background-color:rgb(19, 42, 21);
            color: #fff;
        }


        .spare-part img {
            width: 150px;
            /* Small image size */
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }

        .bg_img {
            width: 100%;
            /* Full width */
            height: 200px;
            /* Small height */
            object-fit: cover;
            /* Ensures the image fills the area without distortion */
            display: block;
            /* Ensures*/
        }

        form {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        form input[type="text"] {
            flex: 1;
            margin-right: 10px;
        }
        .add-to-cart-btn{
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            align-items: end;
            margin: 6px 10px 10px 1px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.11);

        }
        .add-to-cart-btn:hover {
            background-color:rgb(20, 40, 21);
            color: #fff;
        }
        .spare-part-heading{
            font-size: 3.0em;
            color: #333;
            margin: 10px 10px 30px 30px;
        }

    </style>
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006c">
</head>

<body>
    <!-- Menu Bar -->
    <div class="menu-bar">
        <ul>
            <li><a href="pages/home.php">Home</a></li>
            <li><a href="pages/spareparts.php">Products</a></li>
            <li><a href="pages/user_details.php">Profile</a></li>
            <li><a href="pages/livesupport.php">Support</a></li>
            <li><form method="POST" action="auth/logout.php" class="logout-form" onsubmit="return confirm('Are you sure you want to logout?');"><input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"><button type="submit" class="logout-button">Logout</button></form></li>
            <!-- Add View Cart and Orders buttons as list items -->
             <div class="buttons-container">
            <a href="cart/cart.php" class="button-link">View Cart</a>
            <a href="orders/ordersview.php" class="button-link">View Orders</a>
        </div>
        </ul>
    </div>


    <!-- Full-width image after the menu bar -->
    <img class="bg_img" src="assets/images/banners/sparepartt up_img.jpg" alt="Spare Parts Banner" class="hero-image">



    <!-- Spare Parts Section -->
    <div class="container">
        <!-- Search Bar -->
        <form method="GET" action="" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search spare parts..." value="<?= htmlspecialchars($search_query) ?>" style="padding: 10px; width: 60%; border: 1px solid #ccc; border-radius: 60px;">
            <button type="submit" style="padding: 10px 20px; background-color:rgb(61, 62, 61); color: white; border: none; border-radius: 15px; cursor: pointer;  ">Search</button>
        </form>
        <h1 class="spare-part-heading">Spare Parts</h1>


        <div id="spare-parts-container">
            <?php if (empty($spare_parts)): ?>
                <p>No results found for "<?= htmlspecialchars($search_query) ?>". Please try another search term.</p>
            <?php else: ?>
                <?php foreach ($spare_parts as $part): ?>
                    <div class="spare-part">
                        <img src="<?= htmlspecialchars(product_image_url($part['image_path']), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($part['item_name'], ENT_QUOTES, 'UTF-8') ?>" class="spare-part-image">
                        <h3>
                            <a href="pages/spare_part_details.php?id=<?= htmlspecialchars($part['spare_id']) ?>">
                                <?= htmlspecialchars($part['item_name']) ?>
                            </a>
                        </h3>
                        <p><?= htmlspecialchars($part['description']) ?></p>
                        <p class="price">Price: $<?= htmlspecialchars($part['price']) ?></p>
                        <p>
                            <?php if ($part['stock'] > 0): ?>
                                <span class="status in-stock">In Stock</span>
                            <?php else: ?>
                                <span class="status out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                        <button class="add-to-cart-btn" onclick="addToCart(<?= htmlspecialchars($part['spare_id']) ?>)">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Spare parts pages">
                <?php if ($page > 1): ?>
                    <a class="pagination-link" href="<?= htmlspecialchars(app_url('pages/spareparts.php') . '?' . http_build_query(array_merge($paginationQuery, ['page' => $page - 1])), ENT_QUOTES, 'UTF-8') ?>" aria-label="Previous page">Previous</a>
                <?php else: ?>
                    <span class="pagination-link is-disabled" aria-disabled="true">Previous</span>
                <?php endif; ?>

                <?php for ($pageNumber = $pageWindowStart; $pageNumber <= $pageWindowEnd; $pageNumber++): ?>
                    <?php if ($pageNumber === $page): ?>
                        <span class="pagination-link is-current" aria-current="page"><?= $pageNumber ?></span>
                    <?php else: ?>
                        <a class="pagination-link" href="<?= htmlspecialchars(app_url('pages/spareparts.php') . '?' . http_build_query(array_merge($paginationQuery, ['page' => $pageNumber])), ENT_QUOTES, 'UTF-8') ?>"><?= $pageNumber ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a class="pagination-link" href="<?= htmlspecialchars(app_url('pages/spareparts.php') . '?' . http_build_query(array_merge($paginationQuery, ['page' => $page + 1])), ENT_QUOTES, 'UTF-8') ?>" aria-label="Next page">Next</a>
                <?php else: ?>
                    <span class="pagination-link is-disabled" aria-disabled="true">Next</span>
                <?php endif; ?>
            </nav>
            <p class="pagination-summary">Showing <?= $offset + 1 ?>–<?= min($offset + count($spare_parts), $totalItems) ?> of <?= $totalItems ?> products</p>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
        function addToCart(spareId) {
            fetch('actions/cart/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        spare_id: spareId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Added to cart successfully!');
                    } else {
                        alert('Failed to add to cart: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('An error occurred: ' + error.message);
                });
        }

        function confirmLogout(event) {
            return confirm('Are you sure you want to log out?');
        }
    </script>
</body>

</html>