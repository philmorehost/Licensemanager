<?php
require_once '../../src/db.php';

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

$page_title = 'Manage Licenses';
$active_page = 'licenses';
require_once '../../src/includes/admin_header.php';
?>

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

<?php require_once '../../src/includes/admin_footer.php'; ?>
