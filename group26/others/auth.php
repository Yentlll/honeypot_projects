<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: dashboard.php");
        exit;
    }
}

function getCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $staticSecret = 'c7a1f0c9e2b54d18b7f3a4d9627f1c5e_2026_SpinVault';
        $timestamp    = microtime(true);
        $randomPart   = bin2hex(random_bytes(32));

        $_SESSION['csrf_token'] = hash(
            'sha256',
            $staticSecret . '|' . $timestamp . '|' . $randomPart
        );
    }

    return $_SESSION['csrf_token'];
}

function validateCsrfToken($token) {
    return !empty($_SESSION['csrf_token'])
        && !empty($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}
?>
