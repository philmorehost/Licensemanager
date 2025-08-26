<?php
require_once '../../src/db.php';

$settings_file = '../../src/settings.json';
$success_message = '';
$error_message = '';

// Load existing settings
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['site_name'] = $_POST['site_name'] ?? '';
    $settings['language'] = $_POST['language'] ?? 'en';
    $settings['paystack_public_key'] = $_POST['paystack_public_key'] ?? '';
    $settings['paystack_secret_key'] = $_POST['paystack_secret_key'] ?? '';
    $settings['currency'] = $_POST['currency'] ?? 'USD';
    $settings['bank_details'] = $_POST['bank_details'] ?? '';
    $settings['smtp_host'] = $_POST['smtp_host'] ?? '';
    $settings['smtp_port'] = $_POST['smtp_port'] ?? '';
    $settings['smtp_user'] = $_POST['smtp_user'] ?? '';
    $settings['admin_email'] = $_POST['admin_email'] ?? '';

    if (!empty($_POST['smtp_pass'])) {
        $settings['smtp_pass'] = $_POST['smtp_pass'];
    }

    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $logo_filename = "logo." . pathinfo($_FILES["site_logo"]["name"], PATHINFO_EXTENSION);
        $target_file = $target_dir . $logo_filename;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['site_logo']['type'], $allowed_types)) {
             $error_message = "Sorry, only JPG, PNG & GIF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["site_logo"]["tmp_name"], $target_file)) {
                $settings['site_logo'] = 'uploads/' . $logo_filename;
            } else {
                $error_message = "Sorry, there was an error uploading your file.";
            }
        }
    }

    if (empty($error_message)) {
        file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $success_message = 'Settings saved successfully!';
    }
}

// Get available languages
$available_languages = [];
$lang_dir = '../../src/language/';
if (is_dir($lang_dir)) {
    $lang_files = scandir($lang_dir);
    foreach ($lang_files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $available_languages[] = pathinfo($file, PATHINFO_FILENAME);
        }
    }
}

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$webhook_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(__DIR__ . '/../'));
$webhook_url = $base_url . $webhook_path . '/webhook.php';

$page_title = 'Settings';
$active_page = 'settings';
require_once '../../src/includes/admin_header.php';
?>

<h2 class="mb-4">Settings</h2>

<?php if ($success_message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
<?php endif; ?>
<?php if ($error_message): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">Site Settings</div>
                <div class="card-body">
                    <div class="mb-3"><label for="site_name" class="form-label">Site Name</label><input type="text" class="form-control" id="site_name" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>"></div>
                    <div class="mb-3"><label for="site_logo" class="form-label">Site Logo</label><input class="form-control" type="file" id="site_logo" name="site_logo"><?php if (!empty($settings['site_logo'])): ?><div class="mt-2"><img src="../<?= htmlspecialchars($settings['site_logo']) ?>" alt="Current Logo" style="max-height: 50px;"></div><?php endif; ?></div>
                    <div class="mb-3">
                        <label for="language" class="form-label">Default Language</label>
                        <select class="form-select" id="language" name="language">
                            <?php foreach ($available_languages as $lang_code): ?>
                                <option value="<?= htmlspecialchars($lang_code) ?>" <?= ($settings['language'] ?? 'en') === $lang_code ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(strtoupper($lang_code)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">Payment Settings</div>
                <div class="card-body">
                    <div class="mb-3"><label for="currency" class="form-label">Currency Code</label><input type="text" class="form-control" id="currency" name="currency" value="<?= htmlspecialchars($settings['currency'] ?? 'USD') ?>" placeholder="e.g., USD, NGN, EUR"></div>
                    <hr>
                    <h5 class="mb-3">Paystack</h5>
                    <div class="mb-3"><label for="paystack_public_key" class="form-label">Paystack Public Key</label><input type="text" class="form-control" id="paystack_public_key" name="paystack_public_key" value="<?= htmlspecialchars($settings['paystack_public_key'] ?? '') ?>"></div>
                    <div class="mb-3"><label for="paystack_secret_key" class="form-label">Paystack Secret Key</label><input type="text" class="form-control" id="paystack_secret_key" name="paystack_secret_key" value="<?= htmlspecialchars($settings['paystack_secret_key'] ?? '') ?>"></div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">Bank Transfer Settings</div>
                <div class="card-body">
                    <div class="mb-3"><label for="bank_details" class="form-label">Bank Account Details</label><textarea class="form-control" id="bank_details" name="bank_details" rows="4" placeholder="Bank Name: ...&#10;Account Number: ...&#10;Account Name: ..."><?= htmlspecialchars($settings['bank_details'] ?? '') ?></textarea><small class="form-text text-muted">These details will be shown to users who choose the bank transfer option.</small></div>
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

<?php require_once '../../src/includes/admin_footer.php'; ?>
