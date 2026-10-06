<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_require_csrf_token();
}
$user_id = app_require_authenticated_user();

$conn = app_db_connect();

// Handle form submission for creating an ad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_ad') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $phone_number = trim($_POST['phone_number'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $image_scheme = strtolower((string) parse_url($image_url, PHP_URL_SCHEME));

    if ($title === '' || strlen($title) > 150 || $description === '' || strlen($description) > 5000
        || $category === '' || strlen($category) > 100 || $price === false || $price < 0
        || $phone_number === '' || strlen($phone_number) > 30
        || !filter_var($image_url, FILTER_VALIDATE_URL) || !in_array($image_scheme, ['http', 'https'], true)) {
        http_response_code(400);
        exit('Check the ad details and provide a valid HTTP or HTTPS image URL.');
    }

    // Insert ad into the database
    $sql = "INSERT INTO ads (user_id, title, description, image_url, category, price, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Ad insert prepare failed: ' . $conn->error);
        http_response_code(500);
        exit('Unable to post your ad right now.');
    }

    // Use the correct type string
    $stmt->bind_param("issssds", $user_id, $title, $description, $image_url, $category, $price, $phone_number);


    if ($stmt->execute()) {
        header('Location: ' . app_url('pages/ads.php'));
        exit();
    } else {
        error_log('Ad insert failed: ' . $stmt->error);
        http_response_code(500);
        exit('Unable to post your ad right now.');
    }

    $stmt->close();
}

// Handle ad deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = filter_var($_POST['delete_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($delete_id === false) {
        http_response_code(400);
        exit('Invalid ad.');
    }

    // Check if the logged-in user is the owner of the ad
    $sql = "SELECT user_id FROM ads WHERE ad_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($ad_user_id);
    $found = $stmt->fetch();
    $stmt->close();

    // Only allow deletion if the logged-in user is the creator of the ad
    if (!$found || (int) $ad_user_id !== $user_id) {
        http_response_code(404);
        exit('Ad not found or you are not authorized to delete it.');
    }

    // Delete ad from the database
    $sql = "DELETE FROM ads WHERE ad_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        header('Location: ' . app_url('pages/ads.php'));
        exit();
    } else {
        error_log('Ad delete failed: ' . $stmt->error);
        http_response_code(500);
        exit('Unable to delete the ad right now.');
    }

    $stmt->close();
}

// Fetch all ads to display
$sql = "SELECT a.ad_id, a.title, a.description, a.image_url, a.category, a.price, a.created_at,a.phone_number, u.name, a.user_id as ad_user_id 
        FROM ads a 
        JOIN users u ON a.user_id = u.user_id 
        ORDER BY a.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ads Page</title>
    <style>
        /* General Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Container */
        .container {
            width: 90%;
            height: 100%;
            max-width: 1000px;
            margin: 10px auto;
            padding: 10px 100px;
            background: #fff;
            border-radius: 8px;
            position: relative;
        }

        /* Heading */
        h1 {
            text-align: center;
            color: #444;
            font-size: 3em;
            margin-top: 15px;
            font-weight: 600;
        }

        /* Create Ad Button */
        .create-ad-btn {
            position: fixed;
            top: 15px;
            right: 10px;
            padding: 15px;
            width: 10%;
            border: 1px solid #ccc;
            border-radius: 60px;
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            transition: background-color 0.3s ease;
            z-index: 1000;
        }

        .create-ad-btn:hover {
            background-color: #45a049;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 10;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 40%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }

        /* Form Styling */
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="number"],
        input[type="phonenumber"],
        textarea {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 0 auto;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
            background: #fdfdfd;
        }

        textarea {
            resize: none;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
        }

        /* Ads Display Section */
        .ads-display {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 2fr));
            gap: 20px;
            width: 90%;
            max-width: 1200px;
            margin-top: 20px;
        }

        .ad-box {
            position: relative;
            /* Make the ad-box the reference for the delete button */
            background: linear-gradient(135deg, #ffffff, #f9f9f9);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            padding-top: 15px;
            /* Add padding if needed to avoid overlap */
        }


        .ad-box:hover {
            transform: scale(1.03);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .ad-image {
            background: #e9ecef;
            padding: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .ad-image img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .ad-content {
            padding: 20px;
            text-align: center;
        }

        .ad-box h3 {
            font-size: 1.4em;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
        }

        .ad-box p {
            font-size: 0.95em;
            color: #666;
            line-height: 1.5;
            margin: 10px 0;
        }

        .ad-box .price {
            font-size: 1.2em;
            color: #28a745;
            font-weight: bold;
            margin: 15px 0;
        }

        .ad-box .category {
            font-size: 0.85em;
            color: #888;
            font-style: italic;
        }

        .delete-btn {
            position: fixed;
            /* Position relative to the ad-box */
            top: 25px;
            /* Distance from the top */
            right: 10px;
            /* Distance from the right */
            background-color: #f44336;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            transition: background-color 0.3s ease;
            z-index: 10;
            /* Ensure it stays above other elements */
        }

        .delete-btn:hover {
            background-color: #d32f2f;
        }

        .menu-bar {
            background-color: rgb(255, 255, 255);
            color: #333;
            padding: 30px 0;
            text-align: center;
            box-shadow: 0 4px 60px rgba(0, 0, 0, 0.15);
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
    </style>
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006l">
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

        </ul>
    </div>


    <div class="container">
        <h1>Ads Section</h1>

        <button class="create-ad-btn" onclick="document.getElementById('adFormModal').style.display = 'block'">Post Your Ad</button>

        <!-- Modal for Creating Ad -->
        <div id="adFormModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="document.getElementById('adFormModal').style.display = 'none'">&times;</span>
                <form action="pages/ads.php" method="POST" id="adForm">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="text" name="title" placeholder="Ad Title" required>
                    <textarea name="description" placeholder="Description" rows="4" required></textarea>
                    <input type="text" name="category" placeholder="Category" required>
                    <input type="number" name="price" placeholder="Price" min="0" step="0.01" required>
                    <input type="text" name="phone_number" id="phone_number" placeholder="Enter your phone number" required>
                    <input type="url" name="image_url" placeholder="Image URL (https://...)" required>
                    <input type="hidden" name="action" value="create_ad">
                    <button type="submit">Post Ad</button>
                </form>
            </div>
        </div>

        <!-- Display Ads -->
        <div class="ads-display">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="ad-box">';
                    echo '<div class="ad-image"><img src="' . htmlspecialchars(ad_image_url($row['image_url'] ?? ''), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . '"></div>';
                    echo '<div class="ad-content">';
                    echo '<h3>' . htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') . '</h3>';
                    echo '<p>' . nl2br(htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8')) . '</p>';
                    echo '<div class="price">$' . htmlspecialchars((string) $row['price'], ENT_QUOTES, 'UTF-8') . '</div>';
                    echo '<div class="phone_number">Phone number: ' . htmlspecialchars($row['phone_number'], ENT_QUOTES, 'UTF-8') . '</div>';
                    echo '<div class="category">Category: ' . htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8') . '</div>';
                    echo '<div class="date">Posted on: ' . htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') . '</div>';

                    // Show delete button only if the user is the creator of the ad
                    if ((int) $row['ad_user_id'] === $user_id) {
                        echo '<form method="POST" onsubmit="return confirm(\'Are you sure you want to delete this ad?\');">';
                        echo '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
                        echo '<input type="hidden" name="delete_id" value="' . (int) $row['ad_id'] . '">';
                        echo '<button type="submit" class="delete-btn">Delete</button></form>';
                    }
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo "<p>No ads available.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        // Close the modal if the user clicks outside of it
        window.onclick = function(event) {
            if (event.target === document.getElementById('adFormModal')) {
                document.getElementById('adFormModal').style.display = 'none';
            }
        }

    </script>

</body>

</html>

<?php
$conn->close();
?>