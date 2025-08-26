<?php
require_once '../../src/db.php';

// Load settings for currency
$settings_file = '../../src/settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}

$stmt = $pdo->query("SELECT * FROM packages ORDER BY price");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Manage Packages';
$active_page = 'packages';
require_once '../../src/includes/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Packages</h2>
    <a href="package_form.php" class="btn btn-primary">Add New Package</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Max Licenses</th>
                        <th>Features</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($packages)): ?>
                        <tr><td colspan="5" class="text-center">No packages found.</td></tr>
                    <?php else: ?>
                        <?php foreach($packages as $package): ?>
                        <tr>
                            <td><?= htmlspecialchars($package['name']) ?></td>
                            <td><?= htmlspecialchars($settings['currency'] ?? 'USD') ?> <?= htmlspecialchars(number_format($package['price'], 2)) ?></td>
                            <td><?= $package['max_licenses'] == -1 ? 'Unlimited' : htmlspecialchars($package['max_licenses']) ?></td>
                            <td><?= htmlspecialchars($package['features']) ?></td>
                            <td>
                                <a href="package_form.php?id=<?= $package['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                                <a href="delete_package.php?id=<?= $package['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? Deleting a package will remove it from all users and licenses, but will not delete the users or licenses themselves.')">Delete</a>
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
