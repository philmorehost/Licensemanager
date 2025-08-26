<?php
require_once('db.php');
$license_key = 'N/A';
$error = '';

if (isset($_GET['ref'])) {
    $ref = $_GET['ref'];
    $attempts = 0;
    $max_attempts = 5; // Try 5 times
    $license = null;

    while ($attempts < $max_attempts) {
        try {
            $stmt = $pdo->prepare("SELECT l.license_key FROM licenses l JOIN transactions t ON l.id = t.license_id WHERE t.transaction_ref = ?");
            $stmt->execute([$ref]);
            $license = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($license) {
                $license_key = $license['license_key'];
                $error = ''; // Clear any previous error
                break; // Exit loop if license is found
            }
        } catch (PDOException $e) {
            $error = "A database error occurred. Please contact support.";
            // Stop retrying if a database error occurs
            break;
        }

        // If license not found, set a pending message and wait before retrying
        $error = "Your transaction is being processed. Please wait...";
        $attempts++;
        if ($attempts < $max_attempts) {
            sleep(2); // Wait 2 seconds before the next attempt
        }
    }

    // After all attempts, if license is still not found, set the final error message.
    if (!$license && strpos($error, 'processed') !== false) {
        $error = "Could not find a license for this transaction. Please check your email or contact support with reference: " . htmlspecialchars($ref);
    }

} else {
    $error = "No transaction reference provided.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - License Manager</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; margin: 0; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .container { background: #fff; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); width: 100%; max-width: 36rem; text-align: center; }
        h1 { color: #16a34a; font-size: 2.25rem; margin-bottom: 1rem; }
        p { color: #6b7280; margin-bottom: 2rem; }
        .license-key { background: #f3f4f6; border: 2px dashed #d1d5db; padding: 1rem; border-radius: 0.5rem; font-size: 1.25rem; font-weight: 600; margin-bottom: 2rem; word-wrap: break-word; }
        .btn { background-color: #3b82f6; color: #fff; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 500; display: inline-block; margin-top: 1rem; }
        .error { color: #b91c1c; background-color: #fef2f2; border: 1px solid #fca5a5; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .installation-guide { text-align: left; margin-top: 2.5rem; padding: 1.5rem; background-color: #f9fafb; border-radius: 0.5rem; border: 1px solid #e5e7eb; }
        .installation-guide h3 { margin-top: 0; color: #111827; }
        .installation-guide ol { padding-left: 1.5rem; }
        .installation-guide li { margin-bottom: 0.75rem; }
        .installation-guide code { background-color: #e5e7eb; padding: 0.2rem 0.4rem; border-radius: 0.25rem; font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($error): ?>
            <h1>Error</h1>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>
            <h1>Payment Successful!</h1>
            <p>Thank you for your purchase. Your license key has been sent to your email. You can also copy it from below.</p>
            <div class="license-key" id="licenseKey"><?= htmlspecialchars($license_key) ?></div>
            <button class="btn" onclick="copyLicense()">Copy Key</button>

            <div class="installation-guide">
                <h3>Installation Guide</h3>
                <ol>
                    <li>Download the main script and upload it to the server where you wish to install it.</li>
                    <li>Navigate to the <strong>setup.php</strong> file in your browser to begin installation.</li>
                    <li>You will be asked for the following details on the first step:</li>
                    <ul>
                        <li><strong>License Server URL:</strong> <code><?= htmlspecialchars((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]") ?></code></li>
                        <li><strong>Your License Key:</strong> (The key shown above)</li>
                        <li><strong>Your Domain Name:</strong> The domain you entered during purchase.</li>
                    </ul>
                    <li>Follow the on-screen instructions to complete the installation.</li>
                </ol>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function copyLicense() {
            const licenseKey = document.getElementById('licenseKey').innerText;
            navigator.clipboard.writeText(licenseKey).then(() => {
                alert('License key copied to clipboard!');
            }, (err) => {
                alert('Failed to copy license key.');
            });
        }
    </script>
</body>
</html>
