<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../src/db.php';

// Load settings
$settings_file = '../src/settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}
$paystack_pk = $settings['paystack_public_key'] ?? '';
$currency = $settings['currency'] ?? 'USD';

// Fetch user's email
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user_email = $stmt->fetchColumn();

// Fetch all packages
$stmt = $pdo->query("SELECT * FROM packages ORDER BY price");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase a Package - License Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://js.paystack.co/v1/inline.js"></script>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">License Manager</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="purchase.php">Purchase</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-5">Choose a Package</h2>
        <div class="row justify-content-center">
            <?php foreach ($packages as $package): ?>
                <div class="col-lg-4">
                    <div class="card text-center p-4 mb-4">
                        <h3><?= htmlspecialchars($package['name']) ?></h3>
                        <p class="h1"><?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($package['price'], 2)) ?></p>
                        <ul class="list-unstyled my-4">
                            <?php foreach (explode(',', $package['features']) as $feature): ?>
                                <li><?= htmlspecialchars(trim($feature)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" onclick="payWithPaystack(<?= htmlspecialchars(json_encode($package)) ?>)">
                                Pay with Card
                            </button>
                            <a href="bank_transfer.php?package_id=<?= $package['id'] ?>" class="btn btn-outline-secondary">
                                Pay with Bank Transfer
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function payWithPaystack(package) {
            const handler = PaystackPop.setup({
                key: '<?= $paystack_pk ?>',
                email: '<?= $user_email ?>',
                amount: package.price * 100, // Amount in kobo/cents
                currency: '<?= $currency ?>',
                ref: 'lic-' + Math.floor((Math.random() * 1000000000) + 1),
                metadata: {
                    user_id: <?= $_SESSION['user_id'] ?>,
                    package_id: package.id,
                    username: '<?= $_SESSION['username'] ?? '' ?>'
                },
                callback: function(response) {
                    alert('Payment successful! Your account will be updated shortly.');
                    window.location.href = 'dashboard.php';
                },
                onClose: function() {
                    alert('Transaction was not completed.');
                }
            });
            handler.openIframe();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
