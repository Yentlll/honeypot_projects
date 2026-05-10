<?php
require "../others/db.php";
require "../others/auth.php";
requireLogin();

$symbols = ["♠", "♥", "♦", "♣", "★"];
$message = "";

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$r1 = $r2 = $r3 = "-";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid request token.";
    } else {
        $rawBet = (int)$_POST["bet"];

        if ($rawBet > 100000) {
            $message = "You can only bet a maximum of 100,000 credits per spin.";
        } else {
            $bet = max(1, $rawBet);

            if ($user["balance"] >= $bet) {
                $r1 = $symbols[array_rand($symbols)];
                $r2 = $symbols[array_rand($symbols)];
                $r3 = $symbols[array_rand($symbols)];

                $payout = 0;
                $result = "lose";

                if ($r1 === $r2 && $r2 === $r3) {
                    $payout = $bet * 5;
                    $result = "jackpot";
                } elseif ($r1 === $r2 || $r2 === $r3 || $r1 === $r3) {
                    $payout = $bet * 2;
                    $result = "win";
                }

                $newBalance = $user["balance"] - $bet + $payout;

                $stmt = $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $stmt->execute([$newBalance, $_SESSION["user_id"]]);

                $stmt = $pdo->prepare("INSERT INTO spins (user_id, bet_amount, reel1, reel2, reel3, result, payout) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION["user_id"], $bet, $r1, $r2, $r3, $result, $payout]);

                $user["balance"] = $newBalance;
                unset($_SESSION['csrf_token']);
                $message = "Result: $r1 | $r2 | $r3 - payout: $payout credits!";
                
            } else {
                $message = "Not enough balance.";
            }
        }
    }
}
include "../others/header.php";
?>

<section class="slot-layout">
    <div class="card slot-card">
        <h1>Slot Machine</h1>
        <p>Balance: <?= (int)$user["balance"] ?> credits</p>

        <div class="slot-reels">
            <div class="reel"><?= e($r1) ?></div>
            <div class="reel"><?= e($r2) ?></div>
            <div class="reel"><?= e($r3) ?></div>
        </div>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">

            <label for="bet">Bet amount (Max: 100,000)</label>
            <input type="number" id="bet" name="bet" min="1" max="100000" value="10" required>

            <button class="btn" type="submit">Spin</button>
        </form>

        <p><strong><?= e($message) ?></strong></p>
    </div>
</section>

<?php include "../others/footer.php"; ?>
