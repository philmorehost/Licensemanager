<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../src/db.php';

// Flash messages
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);

// Fetch user data, including package info
$stmt = $pdo->prepare("
    SELECT u.api_key, u.package_id, p.name as package_name, p.max_licenses
    FROM users u
    LEFT JOIN packages p ON u.package_id = p.id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$api_key = $user['api_key'] ?? '';

// Fetch user's licenses
$stmt = $pdo->prepare("
    SELECT l.id, l.license_key, l.domain, l.status, p.name as package_name
    FROM licenses l
    LEFT JOIN packages p ON l.package_id = p.id
    WHERE l.user_id = ?
    ORDER BY l.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
$license_count = count($licenses);

$page_title = 'User Dashboard';
require_once '../src/includes/header.php';
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h2>
        <a href="integration_guide.php" class="btn btn-outline-info">View Integration Guide</a>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Your API Key</div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" value="<?= htmlspecialchars($api_key) ?>" id="apiKey" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyApiKey()">Copy</button>
                    </div>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">Your Current Package</div>
                <div class="card-body">
                    <?php if ($user['package_id']): ?>
                        <h5 class="card-title"><?= htmlspecialchars($user['package_name']) ?></h5>
                        <p>
                            Licenses Used: <?= $license_count ?> /
                            <?= $user['max_licenses'] == -1 ? 'Unlimited' : $user['max_licenses'] ?>
                        </p>
                        <a href="purchase.php" class="btn btn-primary">Upgrade Package</a>
                    <?php else: ?>
                        <p>You do not have an active package.</p>
                        <a href="purchase.php" class="btn btn-primary">Purchase a Package</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">Create New License</div>
                <div class="card-body">
                    <form action="create_license.php" method="POST">
                        <div class="mb-3">
                            <label for="domain" class="form-label">Domain Name</label>
                            <input type="text" class="form-control" id="domain" name="domain" placeholder="example.com" required>
                        </div>
                        <button type="submit" class="btn btn-success" <?= !$user['package_id'] ? 'disabled' : '' ?>>Create License</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">Your Licenses</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>License Key</th><th>Domain</th><th>Package</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($licenses)): ?>
                            <tr><td colspan="5" class="text-center">You have not created any licenses yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($licenses as $license): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($license['license_key']) ?></code></td>
                                    <td><?= htmlspecialchars($license['domain']) ?></td>
                                    <td><?= htmlspecialchars($license['package_name'] ?? 'N/A') ?></td>
                                    <td><span class="badge bg-success"><?= htmlspecialchars($license['status']) ?></span></td>
                                    <td>
                                        <a href="download_pinger.php?license_id=<?= $license['id'] ?>" class="btn btn-sm btn-secondary">Download Pinger</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function copyApiKey() {
        const apiKeyInput = document.getElementById('apiKey');
        apiKeyInput.select();
        apiKeyInput.setSelectionRange(0, 99999);
        document.execCommand('copy');
        alert('API Key copied to clipboard!');
    }
</script>

<?php require_once '../src/includes/footer.php'; ?>
