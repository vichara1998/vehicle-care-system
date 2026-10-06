<?php
require_once __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_require_csrf_token();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $passwordConfirmation = $_POST['password_confirmation'] ?? '';

    if ($name === '' || strlen($name) > 100) {
        $error = 'Enter a name up to 100 characters.';
    } elseif ($email === false) {
        $error = 'Enter a valid email address.';
    } elseif (!is_string($password) || !app_password_is_valid($password)) {
        $error = 'Use a password with at least 12 characters and no more than 72 bytes.';
    } elseif (!is_string($passwordConfirmation) || !hash_equals($password, $passwordConfirmation)) {
        $error = 'The passwords do not match.';
    }

    if (!isset($error)) {
        $conn = app_db_connect();

        $stmt = $conn->prepare('SELECT email FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Email already exists. Please use a different email.';
        } else {
            $stmt->close();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $passwordHash);

            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                header('Location: ' . app_url('auth/login.php'));
                exit();
            }

            error_log('Registration insert failed: ' . $stmt->error);
            $error = 'Unable to create the account right now. Please try again.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <base href="<?= htmlspecialchars(app_url(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/app-ui.css?v=20261006l">
</head>
<body class="img-background">
    <div class="main-border">
        <h2>Register</h2>

        <!-- Display Error Message -->
        <?php if (isset($error)): ?><p class="error-message" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>

        <form method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(app_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="register-name">Name</label>
            <input type="text" name="name" placeholder="Name" required id="register-name" autocomplete="name" maxlength="100"><br>
            <label for="register-email">Email</label>
            <input type="email" name="email" placeholder="Email" required id="register-email" autocomplete="email" maxlength="254"><br>
            <label for="register-password">Password</label>
            <input type="password" name="password" placeholder="At least 12 characters" required id="register-password" autocomplete="new-password" minlength="12" maxlength="72" aria-describedby="password-guidance"><br>
            <small id="password-guidance">Use at least 12 characters and no more than 72 bytes. A long, unique passphrase is recommended.</small>
            <label for="register-password-confirmation">Confirm password</label>
            <input type="password" name="password_confirmation" placeholder="Confirm password" required id="register-password-confirmation" autocomplete="new-password" minlength="12" maxlength="72"><br>
            <button type="submit">Register</button>
        </form>

        <!-- login Button -->
        <div style="margin-top: 20px; text-align: center;">
            <a href="auth/login.php" style="text-decoration: none; font-size: 20px; color: white; background-color: #007bff; padding: 10px 20px; border-radius: 20px;">Login</a>
        </div>
    </div>

</body>
</html>
