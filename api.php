<?php
header('Content-Type: application/json');

require_once('db.php');

// Function to log debug messages
function api_log($message) {
    file_put_contents('api_debug.log', date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
}

api_log("--- New API Request ---");

$response = ['status' => 0, 'message' => 'Invalid license key or domain.'];

if (isset($_POST['key']) && isset($_POST['domain'])) {
    $key = $_POST['key'];
    $domain = $_POST['domain'];
    api_log("Received key: {$key}, domain: {$domain}");

    try {
        $stmt = $pdo->prepare("SELECT * FROM licenses WHERE license_key = ? AND domain = ? AND status = 'active'");
        $stmt->execute([$key, $domain]);
        $license = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($license) {
            $response['status'] = 1;
            $response['message'] = 'License is valid.';
            api_log("SUCCESS: Found matching active license for domain '{$domain}'.");
        } else {
            // Additional check to see if the key exists at all, for better debugging
            $stmt_key_only = $pdo->prepare("SELECT * FROM licenses WHERE license_key = ?");
            $stmt_key_only->execute([$key]);
            $key_exists = $stmt_key_only->fetch(PDO::FETCH_ASSOC);
            if (!$key_exists) {
                api_log("FAILURE: License key '{$key}' does not exist.");
            } else {
                api_log("FAILURE: License key '{$key}' exists, but does not match domain '{$domain}' or is not active. DB Domain: '{$key_exists['domain']}', DB Status: '{$key_exists['status']}'.");
            }
        }
    } catch (PDOException $e) {
        $response['message'] = 'A database error occurred.';
        api_log("DATABASE ERROR: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Missing key or domain parameter.';
    api_log("ERROR: Missing key or domain in POST request. Data: " . json_encode($_POST));
}

api_log("Responding with: " . json_encode($response));
echo json_encode($response);
?>
