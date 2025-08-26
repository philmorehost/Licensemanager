<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

// Include the language system
require_once __DIR__ . '/language.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Admin Panel') ?> - License Manager</title>
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
            <li class="nav-item"><a href="dashboard.php" class="nav-link <?= ($active_page ?? '') === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
            <li><a href="licenses.php" class="nav-link <?= ($active_page ?? '') === 'licenses' ? 'active' : '' ?>">Licenses</a></li>
            <li><a href="transactions.php" class="nav-link <?= ($active_page ?? '') === 'transactions' ? 'active' : '' ?>">Transactions</a></li>
            <li><a href="settings.php" class="nav-link <?= ($active_page ?? '') === 'settings' ? 'active' : '' ?>">Settings</a></li>
            <li><a href="profile.php" class="nav-link <?= ($active_page ?? '') === 'profile' ? 'active' : '' ?>">Profile</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="main-content">
    <!-- Page content starts here -->
