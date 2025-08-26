<?php
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

$page_title = 'Admin Profile';
$active_page = 'profile';
require_once '../../src/includes/admin_header.php';
?>

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

<?php require_once '../../src/includes/admin_footer.php'; ?>
