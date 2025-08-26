<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the language system
require_once __DIR__ . '/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'License Manager') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; background-color: #f8f9fa; }
        .main-content { flex: 1; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,.1); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">License Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Menu for logged-in users -->
                        <li class="nav-item"><a class="nav-link" href="dashboard.php"><?= trans('dashboard') ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="purchase.php"><?= trans('purchase_upgrade') ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="api_docs.php"><?= trans('api_docs') ?></a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php"><?= trans('logout') ?></a></li>
                    <?php else: ?>
                        <!-- Menu for logged-out users -->
                        <li class="nav-item"><a class="nav-link" href="index.php#features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php#pricing">Pricing</a></li>
                        <li class="nav-item"><a class="nav-link" href="api_docs.php">API Docs</a></li>
                        <li class="nav-item"><a class="btn btn-outline-light me-2" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="btn btn-primary" href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
    <!-- Page content starts here -->
