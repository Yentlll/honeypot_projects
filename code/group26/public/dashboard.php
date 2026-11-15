<?php
require "../others/db.php";
require "../others/auth.php";
requireLogin();

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT COUNT(*) FROM spins WHERE user_id = ?");
$stmt2->execute([$_SESSION["user_id"]]);
$totalSpins = $stmt2->fetchColumn();

include "../others/header.php";
?>

<section class="dashboard-head">
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($user["username"]) ?>.</p>
</section>

<section class="grid">
    <article class="card">
        <h2>Balance</h2>
        <p><?= (int)$user["balance"] ?> credits</p>
    </article>
    <article class="card">
        <h2>Total spins</h2>
        <p><?= (int)$totalSpins ?></p>
    </article>
    <article class="card">
        <h2>Role</h2>
        <p><?= htmlspecialchars($user["role"]) ?></p>
    </article>
</section>

<?php include "../others/footer.php"; ?>
