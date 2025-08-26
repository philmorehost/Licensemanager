<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';

$transaction_id = $_GET['id'] ?? null;

if ($transaction_id) {
    try {
        $stmt = $pdo->prepare("UPDATE transactions SET status = 'rejected' WHERE id = ? AND status = 'pending_approval'");
        $stmt->execute([$transaction_id]);
    } catch (PDOException $e) {
        // In a real app, you would log this error
        error_log("Order rejection error: " . $e->getMessage());
    }
}

header('Location: transactions.php');
exit();
?>
