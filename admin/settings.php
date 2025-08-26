<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once('../db.php');

// For simplicity, we'll store settings in a JSON file.
// In a real application, you might use a database table.
$settings_file = '../settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['site_name'] = $_POST['site_name'] ?? '';
    $settings['paystack_public_key'] = $_POST['paystack_public_key'] ?? '';
    $settings['paystack_secret_key'] = $_POST['paystack_secret_key'] ?? '';
    $settings['license_price'] = $_POST['license_price'] ?? '5000.00';
    $settings['smtp_host'] = $_POST['smtp_host'] ?? '';
    $settings['smtp_port'] = $_POST['smtp_port'] ?? '';
    $settings['smtp_user'] = $_POST['smtp_user'] ?? '';
    $settings['smtp_pass'] = $_POST['smtp_pass'] ?? '';
    $settings['admin_email'] = $_POST['admin_email'] ?? '';

    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == 0) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $target_file = $target_dir . basename($_FILES["site_logo"]["name"]);
        if (move_uploaded_file($_FILES["site_logo"]["tmp_name"], $target_file)) {
            $settings['site_logo'] = 'uploads/' . basename($_FILES["site_logo"]["name"]);
        }
    }

    file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT));
    header('Location: settings.php');
    exit();
}

$webhook_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/webhook.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - License Manager</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #111827; color: #fff; display: flex; flex-direction: column; min-height: 100vh; }
        .sidebar h1 { font-size: 1.5rem; padding: 1.5rem; text-align: center; background: #1f2937; margin: 0; }
        .sidebar nav a { display: block; padding: 1rem 1.5rem; color: #d1d5db; text-decoration: none; transition: background-color 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: #374151; color: #fff; }
        .main-content { flex-grow: 1; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .header h2 { font-size: 1.875rem; color: #111827; margin: 0; }
        .btn { background-color: #3b82f6; color: #fff; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; border: none; cursor: pointer; }
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
        .card-body { padding: 1.5rem; }
        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; font-weight: 500; margin-bottom: 0.5rem; }
        .input-group input { width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>License Manager</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="licenses.php">Licenses</a>
            <a href="transactions.php">Transactions</a>
            <a href="settings.php" class="active">Settings</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="header">
            <h2>Settings</h2>
        </div>

        <div class="card">
            <div class="card-header"><h3>Webhook URL</h3></div>
            <div class="card-body">
                <p>Copy this URL and paste it into your Paystack webhook settings.</p>
                <input type="text" value="<?= htmlspecialchars($webhook_url) ?>" readonly>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <div class="card-header"><h3>Site Settings</h3></div>
                <div class="card-body">
                    <div class="input-group">
                        <label>Site Name</label>
                        <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                    </div>
                    <div class="input-group">
                        <label>Site Logo</label>
                        <input type="file" name="site_logo">
                        <?php if (isset($settings['site_logo'])): ?>
                            <img src="../<?= htmlspecialchars($settings['site_logo']) ?>" alt="Current Logo" style="max-height: 50px; margin-top: 1rem;">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>Payment Gateway (Paystack)</h3></div>
                <div class="card-body">
                    <div class="input-group">
                        <label>Paystack Public Key</label>
                        <input type="text" name="paystack_public_key" value="<?= htmlspecialchars($settings['paystack_public_key'] ?? '') ?>">
                    </div>
                    <div class="input-group">
                        <label>Paystack Secret Key</label>
                        <input type="text" name="paystack_secret_key" value="<?= htmlspecialchars($settings['paystack_secret_key'] ?? '') ?>">
                    </div>
                    <div class="input-group">
                        <label>License Price (in NGN)</label>
                        <input type="number" step="0.01" name="license_price" value="<?= htmlspecialchars($settings['license_price'] ?? '5000.00') ?>">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3>SMTP Settings</h3></div>
                <div class="card-body">
                    <div class="input-group"><label>SMTP Host</label><input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>"></div>
                    <div class="input-group"><label>SMTP Port</label><input type="text" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '') ?>"></div>
                    <div class="input-group"><label>SMTP User</label><input type="text" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>"></div>
                    <div class="input-group"><label>SMTP Pass</label><input type="password" name="smtp_pass" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>"></div>
                    <div class="input-group"><label>Admin Email</label><input type="email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>"></div>
                </div>
            </div>

            <button type="submit" class="btn">Save Settings</button>
        </form>
    </div>
</body>
</html>
