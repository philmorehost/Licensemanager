<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['package_id'])) {
    $package_id = (int)$_POST['package_id'];
    $user_id = $_SESSION['user_id'];

    // Check if package exists
    $stmt = $pdo->prepare("SELECT id FROM packages WHERE id = ?");
    $stmt->execute([$package_id]);
    if ($stmt->fetch()) {
        // Update user's package
        $update_stmt = $pdo->prepare("UPDATE users SET package_id = ? WHERE id = ?");
        $update_stmt->execute([$package_id, $user_id]);

        $_SESSION['success_message'] = 'Package updated successfully!';
    } else {
        $_SESSION['error_message'] = 'Invalid package selected.';
    }
} else {
    $_SESSION['error_message'] = 'Invalid request.';
}

header('Location: dashboard.php');
exit();
?>
