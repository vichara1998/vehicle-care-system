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
