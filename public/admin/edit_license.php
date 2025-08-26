<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

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

    // Basic validation
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
$stmt = $pdo->prepare("
    SELECT l.*, u.username
    FROM licenses l
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.id = ?
");
$stmt->execute([$license_id]);
$license = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$license) {
    header('Location: licenses.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit License - License Manager</title>
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
            <li><a href="licenses.php" class="nav-link active">Licenses</a></li>
            <li><a href="transactions.php" class="nav-link">Transactions</a></li>
            <li><a href="settings.php" class="nav-link">Settings</a></li>
            <li><a href="profile.php" class="nav-link">Profile</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="main-content">
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
    </div>
</body>
</html>
