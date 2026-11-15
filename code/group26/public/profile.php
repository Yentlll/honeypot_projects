<?php
require "../others/db.php";
require "../others/auth.php";
requireLogin();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid request token.";
    } else {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $bio = trim($_POST["bio"]);

        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, bio = ? WHERE id = ?");
        $stmt->execute([$username, $email, $bio, $_SESSION["user_id"]]);

        $_SESSION["username"] = $username;
        unset($_SESSION['csrf_token']);
        header("Location: profile.php?success=1");
        exit;
    }
}

if (isset($_GET['success'])) {
    $message = "Profile updated.";
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include "../others/header.php";
?>

<section class="form-card">
    <h1>Profile</h1>
    <p><?= htmlspecialchars($message) ?></p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user["username"]) ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user["email"]) ?>" required>

        <label>Bio</label>
        <textarea name="bio"><?= htmlspecialchars($user["bio"]) ?></textarea>

        <button class="btn" type="submit">Save changes</button>
    </form>
</section>

<?php include "../others/footer.php"; ?>
