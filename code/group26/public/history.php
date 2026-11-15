<?php
require "../others/db.php";
require "../others/auth.php";
requireLogin();

$stmt = $pdo->prepare("SELECT * FROM spins WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$stmt->execute([$_SESSION["user_id"]]);
$spins = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../others/header.php";
?>

<section class="card">
    <h1>Spin history</h1>
    <table>
        <tr>
            <th>Bet</th>
            <th>Reels</th>
            <th>Result</th>
            <th>Payout</th>
            <th>Time</th>
        </tr>
        <?php foreach ($spins as $spin): ?>
        <tr>
            <td><?= (int)$spin["bet_amount"] ?></td>
            <td><?= htmlspecialchars($spin["reel1"] . " | " . $spin["reel2"] . " | " . $spin["reel3"]) ?></td>
            <td><?= htmlspecialchars($spin["result"]) ?></td>
            <td><?= (int)$spin["payout"] ?></td>
            <td><?= htmlspecialchars($spin["created_at"]) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</section>

<?php include "../others/footer.php"; ?>
