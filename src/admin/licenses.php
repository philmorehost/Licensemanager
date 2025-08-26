<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../db.php';

// Handle Search
$search = $_GET['search'] ?? '';
$query = "
    SELECT l.*, u.username, u.email
    FROM licenses l
    LEFT JOIN users u ON l.user_id = u.id
";
$params = [];

if ($search) {
    $query .= " WHERE l.license_key LIKE ? OR l.domain LIKE ? OR u.username LIKE ? OR u.email LIKE ?";
    $search_param = "%{$search}%";
    $params = [$search_param, $search_param, $search_param, $search_param];
}

$query .= " ORDER BY l.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Licenses - License Manager</title>
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
        <h2 class="mb-4">Manage Licenses</h2>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span>All Licenses</span>
                    <form method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </form>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>License Key</th>
                                <th>User</th>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($licenses)): ?>
                                <tr><td colspan="6" class="text-center">No licenses found.</td></tr>
                            <?php else: ?>
                                <?php foreach($licenses as $license): ?>
                                <tr>
                                    <td><code><?= htmlspecialchars($license['license_key']) ?></code></td>
                                    <td><?= htmlspecialchars($license['username'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($license['domain']) ?></td>
                                    <td><span class="badge bg-<?= $license['status'] == 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($license['status']) ?></span></td>
                                    <td><?= date('Y-m-d', strtotime($license['created_at'])) ?></td>
                                    <td>
                                        <a href="edit_license.php?id=<?= $license['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                        <a href="delete_license.php?id=<?= $license['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this license?')">Delete</a>
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
</body>
</html>
