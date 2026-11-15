<?php
require "../others/db.php";
require "../others/auth.php";
requireLogin();

$message = "";

if (isset($_GET['success'])) {
    $message = "Avatar uploaded successfully.";
}

$uploadDir = __DIR__ . "/uploads/";
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$allowedMimeTypes = ['image/jpeg', 'image/png'];
$maxFileSize = 2 * 1024 * 1024;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid request token.";
    } elseif (!isset($_FILES["avatar"])) {
        $message = "No file uploaded.";
    } elseif (!isset($_FILES["avatar"]["error"]) || is_array($_FILES["avatar"]["error"])) {
        $message = "Invalid upload request.";
    } elseif ($_FILES["avatar"]["error"] !== UPLOAD_ERR_OK) {
        $message = "Upload failed.";
    } elseif ($_FILES["avatar"]["size"] > $maxFileSize) {
        $message = "File is too large.";
    } else {
        $tmpPath = $_FILES["avatar"]["tmp_name"];
        $originalName = $_FILES["avatar"]["name"];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions, true)) {
            $message = "Only JPG, JPEG and PNG files are allowed.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tmpPath);

            if (!in_array($mimeType, $allowedMimeTypes, true)) {
                $message = "Invalid file type.";
            } elseif (@getimagesize($tmpPath) === false) {
                $message = "Uploaded file is not a valid image.";
            } else {
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                $stmt->execute([(int)$_SESSION["user_id"]]);
                $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

                $newFileName = bin2hex(random_bytes(16)) . "." . $extension;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($tmpPath, $destination)) {
                    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->execute([$newFileName, (int)$_SESSION["user_id"]]);

                    if (!empty($currentUser["avatar"]) && $currentUser["avatar"] !== "default.png") {
                        $oldFile = $uploadDir . basename($currentUser["avatar"]);
                        if (is_file($oldFile)) {
                            unlink($oldFile);
                        }
                    }

                    unset($_SESSION['csrf_token']);
                    header("Location: avatar.php?success=1");
                    exit;
                } else {
                    $message = "Could not save uploaded file.";
                }
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
$stmt->execute([(int)$_SESSION["user_id"]]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include "../others/header.php";
?>

<section class="form-card">
    <h1>Upload avatar</h1>
    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>

    <?php if (!empty($user["avatar"])): ?>
        <div class="avatar-preview">
            <img src="uploads/<?= htmlspecialchars($user["avatar"]) ?>" alt="Current avatar" width="120">
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(getCsrfToken()) ?>">

        <label for="avatar">Choose avatar</label>
        <input id="avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png" required>

        <button class="btn" type="submit">Upload</button>
    </form>
</section>

<?php include "../others/footer.php"; ?>
