<?php
require_once '../../src/db.php';

$license_id = $_GET['id'] ?? null;
if (!$license_id) {
    header('Location: licenses.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = trim($_POST['domain']);
    $status = $_POST['status'];
    $license_id_post = $_POST['id'];

    if (!empty($domain) && in_array($status, ['active', 'inactive']) && $license_id_post == $license_id) {
        $stmt = $pdo->prepare("UPDATE licenses SET domain = ?, status = ? WHERE id = ?");
        $stmt->execute([$domain, $status, $license_id]);
        header('Location: licenses.php');
        exit();
    } else {
        $error = "Invalid data provided.";
    }
}

// Fetch license data for the form
$stmt = $pdo->prepare("SELECT l.*, u.username FROM licenses l LEFT JOIN users u ON l.user_id = u.id WHERE l.id = ?");
$stmt->execute([$license_id]);
$license = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$license) {
    header('Location: licenses.php');
    exit();
}

$page_title = 'Edit License';
$active_page = 'licenses';
require_once '../../src/includes/admin_header.php';
?>

<h2 class="mb-4">Edit License</h2>

<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        Editing License: <strong><?= htmlspecialchars($license['license_key']) ?></strong>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $license['id'] ?>">
            <div class="mb-3">
                <label class="form-label">User</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($license['username'] ?? 'N/A') ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="domain" class="form-label">Domain</label>
                <input type="text" class="form-control" id="domain" name="domain" value="<?= htmlspecialchars($license['domain']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="active" <?= $license['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $license['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="licenses.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once '../../src/includes/admin_footer.php'; ?>
