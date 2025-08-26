<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once('../db.php');

$success_message = '';
$error = '';
$current_username = '';

// We must have an admin_id in the session now, set by the login page.
if (!isset($_SESSION['admin_id'])) {
    // If not, something is wrong, maybe the user session is old.
    // Forcing a re-login is the safest option.
    header('Location: logout.php');
    exit();
}
$admin_id = $_SESSION['admin_id'];

// Get current admin username to display in the form
try {
    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin) {
        $current_username = $admin['username'];
    } else {
        // This case is unlikely if session is valid, but good to handle.
        $error = "Could not find your admin profile.";
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['password_confirm'];

    if (empty($new_username)) {
        $error = "Username cannot be empty.";
    } elseif (!empty($new_password) && $new_password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            if (!empty($new_password)) {
                // Update both username and password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
                $stmt->execute([$new_username, $password_hash, $admin_id]);
                $success_message = 'Profile updated successfully.';
            } else {
                // Update only username
                $stmt = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
                $stmt->execute([$new_username, $admin_id]);
                $success_message = 'Username updated successfully.';
            }
            // Update the username displayed on the form
            $current_username = $new_username;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
                $error = "That username is already taken.";
            } else {
                $error = "A database error occurred: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - License Manager</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f9fafb; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #111827; color: #fff; display: flex; flex-direction: column; min-height: 100vh; }
        .sidebar h1 { font-size: 1.5rem; padding: 1.5rem; text-align: center; background: #1f2937; margin: 0; }
        .sidebar nav a { display: block; padding: 1rem 1.5rem; color: #d1d5db; text-decoration: none; transition: background-color 0.2s; }
        .sidebar nav a:hover, .sidebar nav a.active { background-color: #374151; color: #fff; }
        .main-content { flex-grow: 1; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .header h2 { font-size: 1.875rem; color: #111827; margin: 0; }
        .btn { background-color: #3b82f6; color: #fff; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; border: none; cursor: pointer; }
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; }
        .card-body { padding: 1.5rem; }
        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; font-weight: 500; margin-bottom: 0.5rem; }
        .input-group input { width: 100%; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid #d1d5db; box-sizing: border-box; }
        .success { color: #166534; background-color: #f0fdf4; border: 1px solid #86efac; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .error { color: #b91c1c; background-color: #fef2f2; border: 1px solid #fca5a5; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>License Manager</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="licenses.php">Licenses</a>
            <a href="transactions.php">Transactions</a>
            <a href="settings.php">Settings</a>
            <a href="profile.php" class="active">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="header">
            <h2>Admin Profile</h2>
        </div>
        <div class="card">
            <div class="card-header"><h3>Update Your Details</h3></div>
            <div class="card-body">
                <?php if (!empty($success_message)): ?>
                    <div class="success"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="input-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($current_username) ?>" required>
                    </div>
                    <div class="input-group">
                        <label>New Password (leave blank to keep current password)</label>
                        <input type="password" name="password">
                    </div>
                    <div class="input-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="password_confirm">
                    </div>
                    <button type="submit" class="btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
