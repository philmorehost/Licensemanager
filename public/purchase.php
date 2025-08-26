<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../src/db.php';

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
                        <p class="h1">$<?= htmlspecialchars(number_format($package['price'], 2)) ?></p>
                        <ul class="list-unstyled my-4">
                            <?php foreach (explode(',', $package['features']) as $feature): ?>
                                <li><?= htmlspecialchars(trim($feature)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <form action="process_purchase.php" method="POST">
                            <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                            <button type="submit" class="btn btn-primary">Choose Plan</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
