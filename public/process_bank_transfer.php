<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_proof'])) {
    $package_id = $_POST['package_id'];
    $amount = $_POST['amount'];
    $currency = $_POST['currency'];
    $user_id = $_SESSION['user_id'];

    $upload_dir = 'uploads/proofs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate a unique filename
    $filename = uniqid('proof_', true) . '.' . strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
    $upload_file = $upload_dir . $filename;

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($_FILES['payment_proof']['type'], $allowed_types)) {
        $_SESSION['error_message'] = 'Invalid file type. Please upload a JPG, PNG, or PDF.';
        header('Location: bank_transfer.php?package_id=' . $package_id);
        exit();
    }

    // Move the uploaded file
    if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_file)) {
        try {
            // Create a pending transaction record
            $stmt = $pdo->prepare(
                "INSERT INTO transactions (user_id, package_id, amount, currency, status, payment_method, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $user_id,
                $package_id,
                $amount,
                $currency,
                'pending_approval',
                'bank_transfer',
                $upload_file
            ]);

            $_SESSION['success_message'] = 'Your proof of payment has been submitted. Your order is now pending approval from an admin.';
            header('Location: dashboard.php');
            exit();

        } catch (PDOException $e) {
            // Log error and redirect
            error_log('Bank transfer processing error: ' . $e->getMessage());
            $_SESSION['error_message'] = 'A database error occurred. Please try again.';
            header('Location: bank_transfer.php?package_id=' . $package_id);
            exit();
        }
    } else {
        $_SESSION['error_message'] = 'There was an error uploading your proof of payment.';
        header('Location: bank_transfer.php?package_id=' . $package_id);
        exit();
    }
} else {
    // Redirect if accessed directly
    header('Location: purchase.php');
    exit();
}
?>
