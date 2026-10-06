<?php
require_once __DIR__ . '/../app/bootstrap.php';

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vehicle_care_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize spare parts array
$spare_parts = [];
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

if (!empty($search_query)) {
    // Search spare parts by item name or description
    $sql = "SELECT spare_id, item_name, description, price,image_path, stock 
            FROM spare_parts 
            WHERE item_name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($sql);
    $search_term = "%" . $search_query . "%";
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Fetch all spare parts
    $sql = "SELECT spare_id, item_name, description, price, image_path,stock FROM spare_parts";
    $result = $conn->query($sql);
}

// Fetch results into an array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $spare_parts[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
</head>

<body>
    <!-- Menu Bar -->
    <div class="menu-bar">
        <ul>
            <li><a href="pages/home.php">Home</a></li>
            <li><a href="pages/spareparts.php">Products</a></li>
            <li><a href="pages/user_details.php">Profile</a></li>
            <li><a href="pages/livesupport.php">Support</a></li>
            <li><a href="auth/logout.php" id="logout-button" onclick="return confirmLogout(event);">Logout</a></li>
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
                        <img src="<?= htmlspecialchars($part['image_path']) ?>" alt="<?= htmlspecialchars($part['item_name']) ?>" class="spare-part-image">
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
    </div>

    <!-- JavaScript -->
    <script>
        function addToCart(spareId) {
            fetch('actions/cart/add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
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