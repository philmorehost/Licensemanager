<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';

$transaction_id = $_GET['id'] ?? null;
if (!$transaction_id) {
    header('Location: transactions.php');
    exit();
}

try {
    // Start a database transaction
    $pdo->beginTransaction();

    // 1. Get the transaction details
    $stmt = $pdo->prepare("SELECT user_id, package_id FROM transactions WHERE id = ? AND status = 'pending_approval'");
    $stmt->execute([$transaction_id]);
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($transaction) {
        $user_id = $transaction['user_id'];
        $package_id = $transaction['package_id'];

        // 2. Update the user's package
        $stmt_update_user = $pdo->prepare("UPDATE users SET package_id = ? WHERE id = ?");
        $stmt_update_user->execute([$package_id, $user_id]);

        // 3. Update the transaction status
        $stmt_update_tx = $pdo->prepare("UPDATE transactions SET status = 'completed' WHERE id = ?");
        $stmt_update_tx->execute([$transaction_id]);
    }

    // Commit the transaction
    $pdo->commit();

} catch (PDOException $e) {
    // Roll back the transaction if something failed
    $pdo->rollBack();
    // In a real app, you would log this error and maybe set a session error message
    error_log("Order approval error: " . $e->getMessage());
}

header('Location: transactions.php');
exit();
?>
