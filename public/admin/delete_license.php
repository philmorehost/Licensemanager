<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';

$license_id = $_GET['id'] ?? null;

if ($license_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM licenses WHERE id = ?");
        $stmt->execute([$license_id]);
    } catch (PDOException $e) {
        // In a real app, you might want to set an error message in the session
        // and handle it on the licenses page. For now, we'll just fail silently.
    }
}

header('Location: licenses.php');
exit();
?>
