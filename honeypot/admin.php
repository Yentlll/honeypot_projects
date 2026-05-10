<?php
session_start();

/* ---------------------------
   LOG ALL REQUESTS (ELK)
----------------------------*/
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$time = date('Y-m-d H:i:s');
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

file_put_contents(
    '/var/log/honeypot/admin.json',
    json_encode([
        "time"       => $time,
        "ip"         => $ip,
        "method"     => $method,
        "uri"        => $uri,
        "user_agent" => $userAgent,
        "post"       => $_POST,
        "get"        => $_GET
    ]) . "\n",
    FILE_APPEND
);

/* ---------------------------
   FAKE LOGIN
----------------------------*/
$validUser = "admin";
$validPass = "admin";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    if ($_POST['username'] === $validUser && $_POST['password'] === $validPass) {
        $_SESSION['admin'] = true;
        $_SESSION['admin_time'] = time();
        header("Location: /admin.php?page=dashboard");
        exit;
    }
}

/* ---------------------------
   PROTECTION (30s session)
----------------------------*/
$loggedIn = isset($_SESSION['admin']) && (time() - $_SESSION['admin_time'] < 30);

/* ---------------------------
   FAKE ACTIONS
----------------------------*/
if ($loggedIn && isset($_GET['page'])) {
    if ($_GET['page'] === 'disable' && isset($_GET['id'])) {
        file_put_contents('/var/log/honeypot/admin_actions.log', "DISABLE USER ID=" . $_GET['id'] . " IP=$ip\n", FILE_APPEND);
        header("Location: /admin.php?page=dashboard");
        exit;
    }
    if ($_GET['page'] === 'delete' && isset($_GET['id'])) {
        file_put_contents('/var/log/honeypot/admin_actions.log', "DELETE USER ID=" . $_GET['id'] . " IP=$ip\n", FILE_APPEND);
        header("Location: /admin.php?page=dashboard");
        exit;
    }
}

/* ---------------------------
   FAKE USERS
----------------------------*/
$users = [
    ["id"=>1, "username"=>"testuser", "email"=>"test@gmail.com",    "role"=>"user",  "enabled"=>1, "balance"=>1010],
    ["id"=>2, "username"=>"admin",    "email"=>"admin@gmail.com",   "role"=>"admin", "enabled"=>1, "balance"=>1000],
    ["id"=>3, "username"=>"khatlev",  "email"=>"khatlev@gmail.com", "role"=>"user",  "enabled"=>1, "balance"=>0],
    ["id"=>4, "username"=>"Aykan",    "email"=>"aykan@gmail.com",   "role"=>"admin", "enabled"=>1, "balance"=>1070],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpinVault Admin</title>
    <link rel="stylesheet" href="/assets/screen.css">
    <style>
        /* ---------- Login ---------- */
        .login-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at top left,  rgba(46,194,126,0.08), transparent 28%),
                radial-gradient(circle at top right, rgba(74,144,226,0.08), transparent 22%),
                #0e1116;
        }
        .login-box {
            background: linear-gradient(180deg, rgba(255,255,255,0.02), transparent), #1b222c;
            border: 1px solid #313d4d;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            padding: 2.5rem;
            width: min(100%, 420px);
        }
        .login-icon { text-align:center; font-size:2.4rem; margin-bottom:0.4rem; }
        .login-box h1 { text-align:center; font-size:1.35rem; color:#edf2f7; margin:0 0 0.3rem; }
        .login-box p.sub { text-align:center; color:#7f8b99; font-size:0.88rem; margin:0 0 1.6rem; }
        .login-box form { display:grid; gap:0.9rem; }
        .login-version { text-align:center; font-size:0.75rem; color:#313d4d; margin-top:1.5rem; }

        /* ---------- Dashboard ---------- */
        .dashboard-wrap { max-width:1100px; margin:2rem auto; padding:0 1.25rem 4rem; }
        .dashboard-head { margin-bottom:1.25rem; }
        .dashboard-head h1 { margin:0 0 0.4rem; }
        .dashboard-head p  { color:#b2bfcc; margin:0; }

        .stats-row {
            display:grid;
            grid-template-columns:repeat(auto-fit, minmax(170px,1fr));
            gap:1rem;
            margin-bottom:1.5rem;
        }
        .stat-box { background:#232c38; border:1px solid #313d4d; border-radius:12px; padding:1rem 1.2rem; }
        .stat-box span   { display:block; color:#7f8b99; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.35rem; }
        .stat-box strong { font-size:1.4rem; color:#edf2f7; }

        .table-card {
            background: linear-gradient(180deg, rgba(255,255,255,0.02), transparent), #1b222c;
            border:1px solid #313d4d;
            border-radius:18px;
            box-shadow:0 10px 30px rgba(0,0,0,0.25);
            padding:1.5rem;
            overflow-x:auto;
        }
        .table-card h2 { margin:0 0 0.25rem; }
        .table-card p.sub { color:#7f8b99; margin:0 0 1.2rem; font-size:0.9rem; }

        table { width:100%; border-collapse:collapse; min-width:600px; }
        th, td { padding:0.9rem 0.75rem; text-align:left; border-bottom:1px solid #313d4d; vertical-align:middle; }
        th { color:#edf2f7; font-size:0.88rem; background:rgba(255,255,255,0.02); }
        td { color:#b2bfcc; }
        tr:hover td { background:rgba(255,255,255,0.02); }

        .badge { display:inline-flex; align-items:center; padding:0.3rem 0.65rem; border-radius:999px; font-size:0.78rem; font-weight:700; }
        .badge-admin  { background:rgba(74,144,226,0.15);  color:#88b8f5; }
        .badge-user   { background:rgba(255,255,255,0.06); color:#b2bfcc; }
        .badge-active { background:rgba(46,194,126,0.15);  color:#7ff0b9; }
        .badge-off    { background:rgba(220,76,100,0.15);  color:#ff9bab; }

        .btn-sm {
            display:inline-flex; align-items:center; justify-content:center;
            padding:0.4rem 0.9rem; border-radius:8px; font-size:0.82rem;
            font-weight:700; cursor:pointer; border:none; transition:0.2s ease;
            background:#dc4c64; color:#fff; text-decoration:none;
        }
        .btn-sm:hover { background:#b83a50; transform:translateY(-1px); }
    </style>
</head>
<body>

<?php if (!$loggedIn): ?>

<!-- ==================== LOGIN ==================== -->
<div class="login-wrap">
    <div class="login-box">
        <div class="login-icon">🔒</div>
        <h1>SpinVault Admin</h1>
        <p class="sub">Restricted area — authorised personnel only</p>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="message message-error">Invalid username or password. Please try again.</div>
        <?php endif; ?>

        <form method="POST" action="/admin.php">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="admin" autocomplete="off" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>

            <button type="submit" style="width:100%;margin-top:0.25rem">Login to Admin Panel</button>
        </form>

        <p class="login-version">SpinVault v2.4.1 &mdash; Admin Console</p>
    </div>
</div>

<?php else: ?>

<!-- ==================== DASHBOARD ==================== -->
<header class="site-header">
    <nav class="nav">
        <a href="index.php" class="logo">SpinVault</a>
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="admin.php?page=dashboard">Admin</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
</header>

<div class="dashboard-wrap">
    <div class="dashboard-head">
        <h1>Admin Panel</h1>
        <p>User management &amp; platform overview.</p>
    </div>

    <div class="stats-row">
        <div class="stat-box">
            <span>Total Users</span>
            <strong><?= count($users) ?></strong>
        </div>
        <div class="stat-box">
            <span>Active</span>
            <strong style="color:#2ec27e"><?= count(array_filter($users, fn($u) => $u['enabled'])) ?></strong>
        </div>
        <div class="stat-box">
            <span>Disabled</span>
            <strong style="color:#dc4c64"><?= count(array_filter($users, fn($u) => !$u['enabled'])) ?></strong>
        </div>
        <div class="stat-box">
            <span>Admins</span>
            <strong style="color:#4a90e2"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></strong>
        </div>
    </div>

    <div class="table-card">
        <h2>All Users</h2>
        <p class="sub">Overview of all registered accounts.</p>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Balance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><strong style="color:#edf2f7"><?= htmlspecialchars($u['username']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <?php if ($u['role'] === 'admin'): ?>
                            <span class="badge badge-admin">Admin</span>
                        <?php else: ?>
                            <span class="badge badge-user">User</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['enabled']): ?>
                            <span class="badge badge-active">&#10003; Active</span>
                        <?php else: ?>
                            <span class="badge badge-off">&#10005; Disabled</span>
                        <?php endif; ?>
                    </td>
                    <td>$<?= number_format($u['balance']) ?></td>
                    <td>
                        <a class="btn-sm"
                           href="admin.php?page=disable&id=<?= (int)$u['id'] ?>"
                           onclick="return confirm('Disable <?= htmlspecialchars($u['username']) ?>?')">
                            Disable
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<footer class="footer">
    <p>SpinVault - Honeypot Project - group26</p>
</footer>
</body>
</html>
