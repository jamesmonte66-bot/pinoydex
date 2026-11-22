
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Allow deletion only through POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: species.php");
    exit;
}

// Verify CSRF token
$token = $_POST['csrf_token'] ?? '';

if (
    !isset($_SESSION['csrf_token']) ||
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'], $token)
) {
    header("Location: species.php?error=invalid_request");
    exit;
}

// Validate species ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: species.php?error=invalid_id");
    exit;
}

try {

    $stmt = $pdo->prepare("
        DELETE FROM species
        WHERE id = ?
    ");

    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        header("Location: species.php?deleted=1");
    } else {
        header("Location: species.php?error=not_found");
    }

    exit;

} catch (PDOException $e) {

    error_log("PinoyDex delete species error: " . $e->getMessage());

    header("Location: species.php?error=delete_failed");
    exit;
}
