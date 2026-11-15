<?php
require "../others/db.php";
require "../others/auth.php";

$message = "";

// login.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid request token.";
    } else {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password_hash"])) {
            if ((int)$user["enabled"] === 0) {
                sleep(2);
                $message = "This account has been disabled.";
            } else {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];
                unset($_SESSION['csrf_token']);
                header("Location: dashboard.php");
                exit;
            }
        } else {
            sleep(2);
            $message = "Invalid credentials.";
        }
    }
}

include "../others/header.php";
?>

<section class="form-card">
    <h1>Login</h1>
    <p><?= htmlspecialchars($message) ?></p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="btn" type="submit">Login</button>
    </form>
</section>

<?php include "../others/footer.php"; ?>
