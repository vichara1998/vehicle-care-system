<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_require_csrf_token();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email === false || !is_string($password) || $password === '' || strlen($password) > 1024) {
        $error = 'Invalid login credentials.';
    } else {

        $conn = app_db_connect();

        // Prepare the query to fetch the user's data
        $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $name, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                $_SESSION['user_id'] = $user_id;
                $_SESSION['name'] = $name;
                $_SESSION['role'] = $role;

                if (password_needs_rehash($hashed_password, PASSWORD_DEFAULT)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $conn->prepare('UPDATE users SET password = ? WHERE user_id = ?');
                    $update->bind_param('si', $newHash, $user_id);
                    $update->execute();
                    $update->close();
                }

                $stmt->close();
                $conn->close();
                header('Location: ' . app_url('pages/home.php'));
                exit();
            }
        }

        $stmt->close();
        $conn->close();
        $error = 'Invalid login credentials.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'forgot_password') {
    $success = 'Password reset is not available yet. Please contact the site administrator for help.';
}
?>

<!DOCTYPE html>
<html>
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006c">
</head>
<body class="img-background">
    <div class="main-border">
        <h2>Login</h2>
        <?php if (isset($error)): ?><p class="error-message" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <?php if (isset($success)): ?><p class="success-message" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

        <!-- Login Form -->
        <form method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="login">
            <label for="login-email">Email</label>
            <input type="email" name="email" placeholder="Email" required id="login-email" autocomplete="username" maxlength="254"><br>
            <label for="login-password">Password</label>
            <input type="password" name="password" placeholder="Password" required id="login-password" autocomplete="current-password" maxlength="1024"><br>
            <button type="submit">Login</button>
        </form>

        <!-- Forgot Password Form -->
        <form method="POST" style="margin-top: 20px;">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="forgot_password">
            <p class="auth-helper">Need help accessing your account?</p>
            <button type="submit" style="background-color: #f39c12;">Forgot Password</button>
        </form>

        <!-- Register Button -->
        <div style="margin-top: 20px; text-align: center;">
            <a href="auth/register.php" style="text-decoration: none; font-size: 20px; color: white; background-color: #007bff; padding: 10px 20px; border-radius: 20px;">Register</a>
        </div>
    </div>
</body>
</html>
