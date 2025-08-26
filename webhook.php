<?php
// Paystack webhook handler

// Get the POST data.
$event = file_get_contents('php://input');

// Load settings to get the secret key
$settings_file = 'settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}

// For security, you should verify the webhook signature.
$paystack_secret_key = $settings['paystack_secret_key'] ?? '';
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

if (!$signature || hash_hmac('sha512', $event, $paystack_secret_key) !== $signature) {
    // Invalid signature
    http_response_code(401);
    exit();
}

$event_data = json_decode($event, true);

// Log the event for debugging
$log_file = 'webhook.log';
$error_log_file = 'error.log';

function log_message($message) {
    global $log_file;
    file_put_contents($log_file, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

log_message("Webhook received.");

if (is_writable($log_file) || !file_exists($log_file)) {
    log_message("Event data: " . $event);
} else {
    log_message("Error: log file is not writable.");
}

if ($event_data['event'] === 'charge.success') {
    log_message("Charge success event received.");
    $customer_email = $event_data['data']['customer']['email'];
    $domain = $event_data['data']['metadata']['domain'] ?? 'N/A';

    // Generate a new license key
    $new_license_key = 'VALID-' . strtoupper(bin2hex(random_bytes(8)));

    // --- Database interaction ---
    log_message("Attempting database interaction.");
    require_once('db.php');
    try {
        $pdo->beginTransaction();

        // Insert license
        log_message("Inserting license...");
        $stmt = $pdo->prepare("INSERT INTO licenses (license_key, domain, customer_email, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$new_license_key, $domain, $customer_email, 'active']);
        $license_id = $pdo->lastInsertId();
        log_message("License inserted with ID: {$license_id}");

        // Insert transaction
        log_message("Inserting transaction...");
        $stmt = $pdo->prepare("INSERT INTO transactions (license_id, transaction_ref, amount, currency, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$license_id, $event_data['data']['reference'], $event_data['data']['amount'] / 100, $event_data['data']['currency'], 'success']);
        log_message("Transaction inserted.");

        $pdo->commit();
        log_message("Database transaction committed.");
    } catch (PDOException $e) {
        $pdo->rollBack();
        log_message("Database error: " . $e->getMessage());
        file_put_contents($error_log_file, "Database error: " . $e->getMessage() . "\n", FILE_APPEND);
        http_response_code(500);
        exit();
    }
    // -----------------------------------------

    // --- Send email notification ---
    log_message("Attempting to send email notification.");
    require 'includes/PHPMailer/src/Exception.php';
    require 'includes/PHPMailer/src/PHPMailer.php';
    require 'includes/PHPMailer/src/SMTP.php';

    $settings_file = 'settings.json';
    $settings = [];
    if (file_exists($settings_file)) {
        $settings = json_decode(file_get_contents($settings_file), true);
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = $settings['smtp_host'] ?? 'smtp.example.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['smtp_user'] ?? 'user@example.com';
        $mail->Password   = $settings['smtp_pass'] ?? 'password';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $settings['smtp_port'] ?? 587;

        //Recipients
        $mail->setFrom($settings['admin_email'] ?? 'from@example.com', $settings['site_name'] ?? 'License Manager');
        $mail->addAddress($customer_email);
        $mail->addBCC($settings['admin_email'] ?? 'admin@example.com');

        //Content
        $mail->isHTML(true);
        $mail->Subject = 'Your New License Key';

        $template = file_get_contents('email_template.html');
        $body = str_replace(
            ['{license_key}', '{domain}', '{site_name}'],
            [$new_license_key, $domain, $settings['site_name'] ?? 'License Manager'],
            $template
        );
        $mail->Body = $body;
        $mail->AltBody = "Thank you for your purchase. Your new license key is: {$new_license_key}";

        $mail->send();
    } catch (Exception $e) {
        // Log email error
        file_put_contents('email.log', "Message could not be sent. Mailer Error: {$mail->ErrorInfo}\n", FILE_APPEND);
    }
    // ------------------------------------
}

http_response_code(200);
?>
