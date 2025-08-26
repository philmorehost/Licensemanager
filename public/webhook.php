<?php
// webhook.php for Paystack

// Log all incoming requests for debugging
$log_file = '../src/logs/webhook.log';
file_put_contents($log_file, "--- New Webhook Request ---\n", FILE_APPEND);
$input = @file_get_contents("php://input");
file_put_contents($log_file, $input . "\n", FILE_APPEND);

require_once '../src/db.php';

// Load Paystack secret key from settings
$settings_file = '../src/settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}
$paystack_sk = $settings['paystack_secret_key'] ?? '';

// If we don't have a secret key, we can't verify, so exit.
if (empty($paystack_sk)) {
    http_response_code(500);
    file_put_contents($log_file, "ERROR: Paystack secret key is not set.\n", FILE_APPEND);
    exit();
}

// Verify the event is from Paystack
if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) || $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $paystack_sk)) {
    http_response_code(401); // Unauthorized
    file_put_contents($log_file, "ERROR: Invalid Paystack signature.\n", FILE_APPEND);
    exit();
}

// Decode the event
$event = json_decode($input);
if (!$event || !isset($event->event)) {
    http_response_code(400); // Bad Request
    file_put_contents($log_file, "ERROR: Invalid JSON or event type missing.\n", FILE_APPEND);
    exit();
}

// Handle the charge.success event
if ($event->event === 'charge.success') {
    $data = $event->data;

    // Check if payment was successful
    if ($data->status === 'success') {
        // Extract metadata
        $metadata = $data->metadata ?? null;
        $user_id = $metadata->user_id ?? null;
        $package_id = $metadata->package_id ?? null;

        if ($user_id && $package_id) {
            try {
                // Start a database transaction
                $pdo->beginTransaction();

                // 1. Update the user's package
                $stmt_update_user = $pdo->prepare("UPDATE users SET package_id = ? WHERE id = ?");
                $stmt_update_user->execute([$package_id, $user_id]);

                // 2. Log the transaction
                $stmt_log_tx = $pdo->prepare(
                    "INSERT INTO transactions (user_id, package_id, transaction_ref, amount, currency, status) VALUES (?, ?, ?, ?, ?, ?)"
                );
                $amount_in_dollars = $data->amount / 100;
                $stmt_log_tx->execute([
                    $user_id,
                    $package_id,
                    $data->reference,
                    $amount_in_dollars,
                    $data->currency,
                    'completed'
                ]);

                // Commit the transaction
                $pdo->commit();
                file_put_contents($log_file, "SUCCESS: Processed transaction {$data->reference} for user {$user_id}.\n", FILE_APPEND);

            } catch (PDOException $e) {
                // Roll back the transaction if something failed
                $pdo->rollBack();
                http_response_code(500);
                file_put_contents($log_file, "DATABASE ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
                exit();
            }
        } else {
            file_put_contents($log_file, "ERROR: Missing user_id or package_id in metadata.\n", FILE_APPEND);
        }
    }
}

// Acknowledge receipt of the event
http_response_code(200);
?>
