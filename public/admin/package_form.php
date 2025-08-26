<?php
require_once '../../src/db.php';

$page_title = 'Add Package';
$active_page = 'packages';

$package = [
    'id' => null,
    'name' => '',
    'price' => '',
    'max_licenses' => '',
    'features' => ''
];

$is_edit_mode = false;
if (isset($_GET['id'])) {
    $is_edit_mode = true;
    $page_title = 'Edit Package';
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$package) {
        // Redirect if package not found
        header('Location: packages.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $max_licenses = trim($_POST['max_licenses']);
    $features = trim($_POST['features']);

    if ($is_edit_mode) {
        $stmt = $pdo->prepare("UPDATE packages SET name = ?, price = ?, max_licenses = ?, features = ? WHERE id = ?");
        $stmt->execute([$name, $price, $max_licenses, $features, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO packages (name, price, max_licenses, features) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $max_licenses, $features]);
    }
    header('Location: packages.php');
    exit();
}

require_once '../../src/includes/admin_header.php';
?>

<h2 class="mb-4"><?= htmlspecialchars($page_title) ?></h2>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($package['id']) ?>">
            <div class="mb-3">
                <label for="name" class="form-label">Package Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($package['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars($package['price']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="max_licenses" class="form-label">Max Licenses</label>
                <input type="number" class="form-control" id="max_licenses" name="max_licenses" value="<?= htmlspecialchars($package['max_licenses']) ?>" required>
                <small class="form-text text-muted">Use -1 for unlimited licenses.</small>
            </div>
            <div class="mb-3">
                <label for="features" class="form-label">Features</label>
                <textarea class="form-control" id="features" name="features" rows="3" required><?= htmlspecialchars($package['features']) ?></textarea>
                <small class="form-text text-muted">Comma-separated list of features.</small>
            </div>
            <button type="submit" class="btn btn-primary">Save Package</button>
            <a href="packages.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once '../../src/includes/admin_footer.php'; ?>
