<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';

$settings_file = '../../src/settings.json';
$success_message = '';

// Load existing settings
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update text-based settings
    $settings['site_name'] = $_POST['site_name'] ?? '';
    $settings['paystack_public_key'] = $_POST['paystack_public_key'] ?? '';
    $settings['paystack_secret_key'] = $_POST['paystack_secret_key'] ?? '';
    $settings['currency'] = $_POST['currency'] ?? 'USD';
    $settings['bank_details'] = $_POST['bank_details'] ?? '';
    $settings['smtp_host'] = $_POST['smtp_host'] ?? '';
    $settings['smtp_port'] = $_POST['smtp_port'] ?? '';
    $settings['smtp_user'] = $_POST['smtp_user'] ?? '';
    $settings['admin_email'] = $_POST['admin_email'] ?? '';

    // Only update password if a new one is provided
    if (!empty($_POST['smtp_pass'])) {
        $settings['smtp_pass'] = $_POST['smtp_pass'];
    }

    // Handle logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../../public/uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $logo_filename = "logo." . pathinfo($_FILES["site_logo"]["name"], PATHINFO_EXTENSION);
        $target_file = $target_dir . $logo_filename;

        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
             $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["site_logo"]["tmp_name"], $target_file)) {
                $settings['site_logo'] = 'uploads/' . $logo_filename;
            } else {
                $error = "Sorry, there was an error uploading your file.";
            }
        }
    }

    // Save settings back to JSON file
    if (!isset($error)) {
        file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $success_message = 'Settings saved successfully!';
    }
}

// Recalculate webhook URL
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$webhook_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__ . '/../../public'));
$webhook_url = $base_url . $webhook_path . '/webhook.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - License Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; background-color: #f8f9fa; }
        .sidebar { width: 250px; background: #212529; color: #fff; flex-shrink: 0; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; background-color: #343a40; }
        .main-content { flex-grow: 1; padding: 2rem; }
    </style>
</head>
<body>
    <div class="sidebar d-flex flex-column p-3">
        <h4 class="text-center">License Manager</h4>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
            <li><a href="licenses.php" class="nav-link">Licenses</a></li>
            <li><a href="transactions.php" class="nav-link">Transactions</a></li>
            <li><a href="settings.php" class="nav-link active">Settings</a></li>
            <li><a href="profile.php" class="nav-link">Profile</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="mb-4">Settings</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">Site Settings</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>">
                            </div>
                             <div class="mb-3">
                                <label for="site_logo" class="form-label">Site Logo</label>
                                <input class="form-control" type="file" id="site_logo" name="site_logo">
                                <?php if (!empty($settings['site_logo'])): ?>
                                    <div class="mt-2"><img src="../<?= htmlspecialchars($settings['site_logo']) ?>" alt="Current Logo" style="max-height: 50px;"></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                     <div class="card mb-4">
                        <div class="card-header">Payment Settings</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency Code</label>
                                <input type="text" class="form-control" id="currency" name="currency" value="<?= htmlspecialchars($settings['currency'] ?? 'USD') ?>" placeholder="e.g., USD, NGN, EUR">
                            </div>
                            <hr>
                            <h5 class="mb-3">Paystack</h5>
                             <div class="mb-3">
                                <label for="paystack_public_key" class="form-label">Paystack Public Key</label>
                                <input type="text" class="form-control" id="paystack_public_key" name="paystack_public_key" value="<?= htmlspecialchars($settings['paystack_public_key'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label for="paystack_secret_key" class="form-label">Paystack Secret Key</label>
                                <input type="text" class="form-control" id="paystack_secret_key" name="paystack_secret_key" value="<?= htmlspecialchars($settings['paystack_secret_key'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                     <div class="card mb-4">
                        <div class="card-header">Bank Transfer Settings</div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="bank_details" class="form-label">Bank Account Details</label>
                                <textarea class="form-control" id="bank_details" name="bank_details" rows="4" placeholder="Bank Name: ...&#10;Account Number: ...&#10;Account Name: ..."><?= htmlspecialchars($settings['bank_details'] ?? '') ?></textarea>
                                <small class="form-text text-muted">These details will be shown to users who choose the bank transfer option.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                     <div class="card mb-4">
                        <div class="card-header">Webhook URL</div>
                        <div class="card-body">
                            <p>Set this as your webhook URL in Paystack.</p>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($webhook_url) ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">SMTP Settings</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="smtp_host" class="form-label">SMTP Host</label><input type="text" class="form-control" id="smtp_host" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>"></div>
                        <div class="col-md-6 mb-3"><label for="smtp_port" class="form-label">SMTP Port</label><input type="text" class="form-control" id="smtp_port" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '') ?>"></div>
                        <div class="col-md-6 mb-3"><label for="smtp_user" class="form-label">SMTP User</label><input type="text" class="form-control" id="smtp_user" name="smtp_user" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>"></div>
                        <div class="col-md-6 mb-3"><label for="smtp_pass" class="form-label">SMTP Pass</label><input type="password" class="form-control" id="smtp_pass" name="smtp_pass" placeholder="Leave blank to keep current"></div>
                        <div class="col-md-12 mb-3"><label for="admin_email" class="form-label">Admin Email (From Address)</label><input type="email" class="form-control" id="admin_email" name="admin_email" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>"></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</body>
</html>
