<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ensure package_id is provided
$package_id = $_GET['package_id'] ?? null;
if (!$package_id) {
    header('Location: purchase.php');
    exit();
}

require_once '../src/db.php';

// Fetch package details
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    // Package not found, redirect
    header('Location: purchase.php');
    exit();
}

// Load settings
$settings_file = '../src/settings.json';
$settings = [];
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
}
$bank_details = $settings['bank_details'] ?? 'Bank details not available. Please contact support.';
$currency = $settings['currency'] ?? 'USD';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Payment - License Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="text-center">Bank Transfer for <?= htmlspecialchars($package['name']) ?> Package</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-center fs-4">Total Amount: <strong><?= htmlspecialchars($currency) ?> <?= htmlspecialchars(number_format($package['price'], 2)) ?></strong></p>
                        <hr>

                        <h4>Payment Instructions:</h4>
                        <p>Please make a direct bank transfer to the following account. Use your username (<strong><?= htmlspecialchars($_SESSION['username']) ?></strong>) as the payment reference.</p>

                        <div class="alert alert-info">
                            <h5 class="alert-heading">Bank Account Details</h5>
                            <pre><?= htmlspecialchars($bank_details) ?></pre>
                        </div>

                        <hr>

                        <h4>Upload Proof of Payment:</h4>
                        <p>After making the payment, please upload a screenshot or receipt as proof of payment. Your order will be approved by an admin once the payment is confirmed.</p>

                        <form action="process_bank_transfer.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                            <input type="hidden" name="amount" value="<?= $package['price'] ?>">
                            <input type="hidden" name="currency" value="<?= $currency ?>">

                            <div class="mb-3">
                                <label for="payment_proof" class="form-label">Payment Proof (JPG, PNG, PDF)</label>
                                <input class="form-control" type="file" id="payment_proof" name="payment_proof" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Submit for Verification</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
