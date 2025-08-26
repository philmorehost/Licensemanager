<?php
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
$days_map = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $days_map[$date] = 0;
}
foreach ($chart_data as $data) {
    $days_map[$data['creation_date']] = (int)$data['license_count'];
}
$chart_labels = array_keys($days_map);
$chart_values = array_values($days_map);

$page_title = 'Admin Dashboard';
$active_page = 'dashboard';
require_once '../../src/includes/admin_header.php';
?>

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
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>License Key</th><th>Domain</th><th>User</th><th>Status</th></tr></thead>
                <tbody>
                    <?php if (empty($recent_licenses)): ?>
                        <tr><td colspan="4" class="text-center">No licenses found recently.</td></tr>
                    <?php else: ?>
                        <?php foreach($recent_licenses as $license): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($license['license_key']) ?></code></td>
                            <td><?= htmlspecialchars($license['domain']) ?></td>
                            <td><?= htmlspecialchars($license['username'] ?? 'N/A') ?></td>
                            <td><span class="badge bg-<?= $license['status'] == 'active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($license['status']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

<?php require_once '../../src/includes/admin_footer.php'; ?>
