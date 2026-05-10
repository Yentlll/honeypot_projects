<?php
require "../others/db.php";
require "../others/auth.php";
requireLogin();
requireAdmin();

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid request token.";
    } else {
        $userId = (int)($_POST["user_id"] ?? 0);
        $action = $_POST["action"] ?? "";

        if ($userId > 0 && $userId !== (int)$_SESSION["user_id"]) {
            if ($action === "disable") {
                $stmt = $pdo->prepare("UPDATE users SET enabled = 0 WHERE id = ?");
                $stmt->execute([$userId]);
                unset($_SESSION['csrf_token']);
                header("Location: administrator.php?success=disabled");
                exit;
            } elseif ($action === "enable") {
                $stmt = $pdo->prepare("UPDATE users SET enabled = 1 WHERE id = ?");
                $stmt->execute([$userId]);
                unset($_SESSION['csrf_token']);
                header("Location: administrator.php?success=enabled");
                exit;
            }
        } else {
            $message = "Invalid admin action.";
        }
    }
}

if (isset($_GET['success'])) {
    if ($_GET['success'] === 'disabled') {
        $message = "User disabled.";
    } elseif ($_GET['success'] === 'enabled') {
        $message = "User enabled.";
    }
}

$users = $pdo->query("SELECT id, username, email, role, enabled, balance, created_at FROM users ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

include "../others/header.php";
?>

<section class="card">
    <h1>Admin panel</h1>
    <p><?= htmlspecialchars($message) ?></p>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Enabled</th>
            <th>Balance</th>
            <th>Action</th>
        </tr>

        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= (int)$u["id"] ?></td>
            <td><?= htmlspecialchars($u["username"]) ?></td>
            <td><?= htmlspecialchars($u["email"]) ?></td>
            <td><?= htmlspecialchars($u["role"]) ?></td>
            <td><?= (int)$u["enabled"] ?></td>
            <td><?= (int)$u["balance"] ?></td>
            <td>
                <?php if ((int)$u["id"] !== (int)$_SESSION["user_id"]): ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">
                        <input type="hidden" name="user_id" value="<?= (int)$u["id"] ?>">

                        <?php if ((int)$u["enabled"] === 1): ?>
                            <input type="hidden" name="action" value="disable">
                            <button type="submit" class="btn">Disable</button>
                        <?php else: ?>
                            <input type="hidden" name="action" value="enable">
                            <button type="submit" class="btn">Enable</button>
                        <?php endif; ?>
                    </form>
                <?php else: ?>
                    <span>Current user</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php include "../others/footer.php"; ?>
