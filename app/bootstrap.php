<?php

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$scriptPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '/', PHP_URL_PATH) ?: '/';
$scriptDirectory = str_replace('\\', '/', dirname($scriptPath));
$applicationPath = preg_replace('~/(auth|pages|cart|orders|actions)(?:/.*)?$~', '', $scriptDirectory);

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', rtrim($applicationPath ?: '', '/'));
}

function app_url(string $path = ''): string
{
    return APP_BASE_PATH . '/' . ltrim($path, '/');
}

function app_db_connect(bool $jsonResponse = false): mysqli
{
    $configPath = dirname(__DIR__) . '/config/database.local.php';
    if (!is_file($configPath)) {
        app_db_connection_failure('Local database configuration file is missing.', $jsonResponse);
    }

    $config = require $configPath;
    if (!is_array($config)
        || !is_string($config['host'] ?? null)
        || !is_string($config['username'] ?? null)
        || !is_string($config['password'] ?? null)
        || !is_string($config['database'] ?? null)
        || trim($config['host']) === ''
        || trim($config['username']) === ''
        || trim($config['database']) === '') {
        app_db_connection_failure('Local database configuration is invalid.', $jsonResponse);
    }

    $port = filter_var($config['port'] ?? 3306, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
    if ($port === false) {
        app_db_connection_failure('Local database port is invalid.', $jsonResponse);
    }

    try {
        $connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database'], $port);
        $connection->set_charset('utf8mb4');
        return $connection;
    } catch (mysqli_sql_exception $exception) {
        app_db_connection_failure('Database connection failed: ' . $exception->getMessage(), $jsonResponse);
    }
}

function app_db_connection_failure(string $reason, bool $jsonResponse): void
{
    error_log($reason);
    http_response_code(500);

    if ($jsonResponse) {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(['success' => false, 'message' => 'Service temporarily unavailable.']));
    }

    exit('Unable to connect to the database. Check the private database configuration.');
}

function app_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function app_password_is_valid(string $password): bool
{
    if (function_exists('mb_strlen')) {
        $length = mb_strlen($password, 'UTF-8');
    } else {
        $length = preg_match_all('/./us', $password);
        if ($length === false) {
            return false;
        }
    }

    return $length >= 12 && strlen($password) <= 72;
}

function app_require_csrf_token(): void
{
    $submittedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!is_string($submittedToken) || !is_string($sessionToken) || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
        http_response_code(403);
        exit('Invalid or expired request token. Refresh the page and try again.');
    }
}

function app_require_authenticated_user(): int
{
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($userId === false || $userId === null) {
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            exit(json_encode(['success' => false, 'message' => 'Please sign in to continue.']));
        }

        header('Location: ' . app_url('auth/login.php'));
        exit;
    }

    return $userId;
}

function product_image_url(?string $imagePath): string
{
    $imagePath = trim(str_replace('\\', '/', $imagePath ?? ''));

    if (preg_match('~^https?://~i', $imagePath)) {
        return $imagePath;
    }

    $filename = basename(rawurldecode(parse_url($imagePath, PHP_URL_PATH) ?: ''));
    $productImage = dirname(__DIR__) . '/assets/images/products/' . $filename;

    if ($filename !== '' && is_file($productImage)) {
        return app_url('assets/images/products/' . rawurlencode($filename));
    }

    return app_url('assets/images/products/image-unavailable.svg');
}

function ad_image_url(?string $imageUrl): string
{
    $imageUrl = trim($imageUrl ?? '');
    $scheme = strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME));

    if (filter_var($imageUrl, FILTER_VALIDATE_URL) && in_array($scheme, ['http', 'https'], true)) {
        return $imageUrl;
    }

    return app_url('assets/images/products/image-unavailable.svg');
}
