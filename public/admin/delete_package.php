<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';

$package_id = $_GET['id'] ?? null;

if ($package_id) {
    try {
        // The foreign key constraints are set to ON DELETE SET NULL,
        // so we don't need to worry about orphaned records.
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        $stmt->execute([$package_id]);
    } catch (PDOException $e) {
        // In a real app, you might want to set an error message
        error_log("Package deletion error: " . $e->getMessage());
    }
}

header('Location: packages.php');
exit();
?>
