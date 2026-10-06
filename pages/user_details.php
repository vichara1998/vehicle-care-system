<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_require_csrf_token();
}
$userId = app_require_authenticated_user();

$conn = app_db_connect();

// Handle form submission to update user details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $dob = $_POST['dob'] ?? '';

    // Ensure all required fields are present
    if ($name === '' || $phone === '' || $city === '' || $dob === '' || strlen($name) > 100 || strlen($phone) > 30 || strlen($city) > 100) {
        $error = "All fields except Email and Role are required.";
    } else {
        // Update query excluding email and role
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, city = ?, dob = ? WHERE user_id = ?");
        $stmt->bind_param('ssssi', $name, $phone, $city, $dob, $userId);

        if ($stmt->execute()) {
            $success = "Details updated successfully.";
            $_SESSION['name'] = $name; // Update session variable
        } else {
            error_log('Profile update failed: ' . $stmt->error);
            $error = 'Unable to update your details right now.';
        }
        $stmt->close();
    }
}


// Fetch user details
$stmt = $conn->prepare("SELECT name, email, phone, city, dob, role FROM users WHERE user_id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($name, $email, $phone, $city, $dob, $role);
$stmt->fetch();
$stmt->close();
$conn->close();

// Handle null values
$name = $name ?? "";
$email = $email ?? "";
$phone = $phone ?? "";
$city = $city ?? "";
$dob = $dob ?? "";
$role = $role ?? "";
?>

<!DOCTYPE html>
<html>
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <title>User Details</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006c">
</head>
<body class="img-background">
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

    <!-- Profile Details -->
    <div class="profile-details">
        <h2 class="custom-h2">Your Details</h2>

        <!-- Error or Success Messages -->
        <?php if (isset($success)): ?><p class="success-message" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <?php if (isset($error)): ?><p class="error-message" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

        <form method="POST" id="edit-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <p>
                <label>Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" disabled>
            </p>
            <p>
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
            </p>
            <p>
                <label>Phone:</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" disabled>
            </p>
            <p>
                <label>City:</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>" disabled>
            </p>
            <p>
                <label>Date of Birth:</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($dob); ?>" disabled>
            </p>
            <p>
                <label>Role:</label>
                <input type="text" name="role" value="<?php echo htmlspecialchars($role); ?>" readonly>
            </p>
            <button type="button" id="edit-button">Edit</button>
            <button type="submit" id="save-button" style="display: none;">Save</button>
        </form>
    </div>

    <script>
    // Enable editing when "Edit" button is clicked
        document.getElementById('edit-button').addEventListener('click', function () {
            document.querySelectorAll('#edit-form input').forEach(input => {
                if (input.name !== 'email' && input.name !== 'role') {
                    input.disabled = false; // Enable all except Email and Role
                }
            });
            document.getElementById('edit-button').style.display = 'none'; // Hide Edit button
            document.getElementById('save-button').style.display = 'inline-block'; // Show Save button
        });
    </script>

</body>
</html>
