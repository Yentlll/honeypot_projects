<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

header(
    "Content-Security-Policy: " .
    "default-src 'self'; " .
    "base-uri 'self'; " .
    "object-src 'none'; " .
    "frame-ancestors 'self'; " .
    "img-src 'self' data:; " .
    "style-src 'self' 'unsafe-inline'; " .
    "script-src 'self'; " .
    "form-action 'self';"
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpinVault</title>
    <link rel="stylesheet" href="/assets/screen.css">
</head>
<body>
<header class="site-header">
    <nav class="nav">
        <a href="index.php" class="logo">SpinVault</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="play.php">Play</a>
                <a href="profile.php">Profile</a>
                <a href="/avatar.php">Avatar</a>
                <a href="history.php">History</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="administrator.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main class="container">
