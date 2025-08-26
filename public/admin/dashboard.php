<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';

// Fetch stats
$total_users = $pdo->query("SELECT count(*) FROM users")->fetchColumn();
$total_licenses = $pdo->query("SELECT count(*) FROM licenses")->fetchColumn();
$active_licenses = $pdo->query("SELECT count(*) FROM licenses WHERE status = 'active'")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'completed'")->fetchColumn();

// Fetch recent licenses
$stmt = $pdo->query("SELECT l.*, u.username FROM licenses l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 10");
$recent_licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch data for chart (licenses created in the last 7 days)
$chart_data_stmt = $pdo->query("
    SELECT DATE(created_at) as creation_date, COUNT(*) as license_count
    FROM licenses
    WHERE created_at >= CURDATE() - INTERVAL 7 DAY
    GROUP BY creation_date
    ORDER BY creation_date ASC
");
$chart_data = $chart_data_stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data for Chart.js
$chart_labels = [];
$chart_values = [];
// Create a map of the last 7 days to 0
$date_range = new DatePeriod(
    new DateTime('-6 days'),
    new DateInterval('P1D'),
    new DateTime('+1 day')
);
$days_map = [];
foreach ($date_range as $date) {
    $days_map[$date->format('Y-m-d')] = 0;
}

foreach ($chart_data as $data) {
    $days_map[$data['creation_date']] = $data['license_count'];
}

foreach ($days_map as $date => $count) {
    $chart_labels[] = date('M d', strtotime($date));
    $chart_values[] = $count;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - License Manager</title>
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
            <li class="nav-item"><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
            <li><a href="licenses.php" class="nav-link">Licenses</a></li>
            <li><a href="transactions.php" class="nav-link">Transactions</a></li>
            <li><a href="settings.php" class="nav-link">Settings</a></li>
            <li><a href="profile.php" class="nav-link">Profile</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="mb-4">Admin Dashboard</h2>

        <div class="row mb-4">
            <div class="col-md-3"><div class="card text-center p-3"><div class="card-body"><h5>Total Users</h5><p class="h2"><?= $total_users ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><div class="card-body"><h5>Total Licenses</h5><p class="h2"><?= $total_licenses ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><div class="card-body"><h5>Active Licenses</h5><p class="h2"><?= $active_licenses ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-center p-3"><div class="card-body"><h5>Total Revenue</h5><p class="h2">$<?= number_format($total_revenue ?? 0, 2) ?></p></div></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header">License Creation (Last 7 Days)</div>
            <div class="card-body">
                <canvas id="licenseChart"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Recent Licenses</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead><tr><th>License Key</th><th>Domain</th><th>User</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach($recent_licenses as $license): ?>
                        <tr>
                            <td><?= htmlspecialchars($license['license_key']) ?></td>
                            <td><?= htmlspecialchars($license['domain']) ?></td>
                            <td><?= htmlspecialchars($license['username'] ?? 'N/A') ?></td>
                            <td><span class="badge bg-<?= $license['status'] == 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($license['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('licenseChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Licenses Created',
                    data: <?= json_encode($chart_values) ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: { scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>
