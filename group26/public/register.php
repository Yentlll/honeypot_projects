<?php
require "../others/db.php";
require "../others/auth.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid request token.";
    } else {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        try {
            $stmt->execute([$username, $email, $password]);
            unset($_SESSION['csrf_token']);
            header("Location: register.php?success=1");
            exit;
        } catch (PDOException $e) {
            $message = "Registration failed.";
        }
    }
}

if (isset($_GET['success'])) {
    $message = "Registration successful. You can now login.";
}

include "../others/header.php";
?>

<section class="form-card">
    <h1>Create account</h1>
    <p><?= htmlspecialchars($message) ?></p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button class="btn" type="submit">Register</button>
    </form>
</section>

<?php include "../others/footer.php"; ?>
