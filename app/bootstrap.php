<?php

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
