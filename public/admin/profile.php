<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

require_once '../../src/db.php';
$admin_id = $_SESSION['admin_id'];

$success_message = '';
$error_message = '';

// Handle Profile Update (Username)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
    $new_username = trim($_POST['username']);
    if (empty($new_username)) {
        $error_message = "Username cannot be empty.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
            $stmt->execute([$new_username, $admin_id]);
            $success_message = "Username updated successfully!";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error_message = "That username is already taken.";
            } else {
                $error_message = "A database error occurred.";
            }
        }
    }
}

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin || !password_verify($current_password, $admin['password'])) {
        $error_message = "Your current password is not correct.";
    } elseif (empty($new_password) || strlen($new_password) < 8) {
        $error_message = "New password must be at least 8 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";
    } else {
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
        $stmt->execute([$new_password_hash, $admin_id]);
        $success_message = "Password changed successfully!";
    }
}

// Get current admin username for display
$stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$current_username = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - License Manager</title>
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
            <li><a href="transactions.php" class="nav-link">Transactions</a></li>
            <li><a href="settings.php" class="nav-link">Settings</a></li>
            <li><a href="profile.php" class="nav-link active">Profile</a></li>
        </ul>
        <hr>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>

    <div class="main-content">
        <h2 class="mb-4">Admin Profile</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">Update Username</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($current_username) ?>" required>
                            </div>
                            <button type="submit" name="update_username" class="btn btn-primary">Save Username</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">Change Password</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
