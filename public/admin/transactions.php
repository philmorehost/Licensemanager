<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';

// Pagination config
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Handle Search
$search = $_GET['search'] ?? '';
$search_params = [];

// Base query for the new schema
$query = "
    SELECT t.*, u.username, p.name as package_name
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN packages p ON t.package_id = p.id
";
$count_query = "
    SELECT count(*)
    FROM transactions t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN packages p ON t.package_id = p.id
";

if ($search) {
    $where_clause = " WHERE t.transaction_ref LIKE ? OR u.username LIKE ? OR p.name LIKE ? OR t.payment_method LIKE ?";
    $query .= $where_clause;
    $count_query .= $where_clause;
    $search_param = "%{$search}%";
    $search_params = [$search_param, $search_param, $search_param, $search_param];
}

// Get total records for pagination
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute($search_params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch records for the current page
$query .= " ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);

foreach ($search_params as $key => $value) {
    $stmt->bindValue($key + 1, $value, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getStatusBadge($status) {
    switch ($status) {
        case 'completed': return 'bg-success';
        case 'pending_approval': return 'bg-warning';
        case 'rejected': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Transactions - License Manager</title>
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
            <li><a href="transactions.php" class="nav-link active">Transactions</a></li>
            <li><a href="settings.php" class="nav-link">Settings</a></li>
            <li><a href="profile.php" class="nav-link">Profile</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="mb-4">Package Transactions</h2>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <span>All Package Purchases</span>
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
                                <th>Date</th>
                                <th>User</th>
                                <th>Package</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr><td colspan="7" class="text-center">No transactions found.</td></tr>
                            <?php else: ?>
                                <?php foreach($transactions as $tx): ?>
                                <tr>
                                    <td><?= date('Y-m-d H:i', strtotime($tx['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($tx['username'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($tx['package_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($tx['currency']) ?> <?= htmlspecialchars(number_format($tx['amount'], 2)) ?></td>
                                    <td><?= htmlspecialchars($tx['payment_method']) ?></td>
                                    <td><span class="badge <?= getStatusBadge($tx['status']) ?>"><?= str_replace('_', ' ', htmlspecialchars($tx['status'])) ?></span></td>
                                    <td>
                                        <?php if ($tx['payment_method'] === 'bank_transfer' && !empty($tx['payment_proof'])): ?>
                                            <a href="../<?= htmlspecialchars($tx['payment_proof']) ?>" class="btn btn-sm btn-info" target="_blank">View Proof</a>
                                        <?php endif; ?>
                                        <?php if ($tx['status'] === 'pending_approval'): ?>
                                            <a href="approve_order.php?id=<?= $tx['id'] ?>" class="btn btn-sm btn-success">Approve</a>
                                            <a href="reject_order.php?id=<?= $tx['id'] ?>" class="btn btn-sm btn-danger">Reject</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= htmlspecialchars($search) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>
</html>
