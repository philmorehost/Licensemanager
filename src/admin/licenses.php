<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit();
}

require_once('../db.php');

// Handle POST requests for license management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_license'])) {
        $stmt = $pdo->prepare("INSERT INTO licenses (license_key, domain, customer_email, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['license_key'], $_POST['domain'], $_POST['customer_email'], $_POST['status']]);
    } elseif (isset($_POST['edit_license'])) {
        $stmt = $pdo->prepare("UPDATE licenses SET license_key = ?, domain = ?, customer_email = ?, status = ? WHERE id = ?");
        $stmt->execute([$_POST['license_key'], $_POST['domain'], $_POST['customer_email'], $_POST['status'], $_POST['id']]);
    } elseif (isset($_POST['toggle_status'])) {
        $stmt = $pdo->prepare("UPDATE licenses SET status = ? WHERE id = ?");
        $new_status = $_POST['current_status'] === 'active' ? 'inactive' : 'active';
        $stmt->execute([$new_status, $_POST['id']]);
    }
    header('Location: licenses.php');
    exit();
}

// Fetch all licenses
$stmt = $pdo->query("SELECT * FROM licenses ORDER BY created_at DESC");
$licenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Licenses - License Manager</title>
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
        .btn { background-color: #3b82f6; color: #fff; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; }
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05); }
        .card-header { padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; }
        .card-body { padding: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; }
        .action-links a { margin-right: 0.5rem; color: #3b82f6; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h1>License Manager</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="licenses.php" class="active">Licenses</a>
            <a href="transactions.php">Transactions</a>
            <a href="settings.php">Settings</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="header">
            <h2>Manage Licenses</h2>
            <a href="licenses.php?action=add" class="btn">Add New License</a>
        </div>

        <?php if (isset($_GET['action']) && $_GET['action'] == 'add'): ?>
        <div class="card">
            <div class="card-header"><h3>Add New License</h3></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="add_license" value="1">
                    <div class="input-group"><label>License Key</label><input type="text" name="license_key" required></div>
                    <div class="input-group"><label>Domain</label><input type="text" name="domain" required></div>
                    <div class="input-group"><label>Customer Email</label><input type="email" name="customer_email" required></div>
                    <div class="input-group"><label>Status</label><select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
                    <button type="submit" class="btn">Save License</button>
                </form>
            </div>
        </div>
        <?php elseif (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])):
            $stmt = $pdo->prepare("SELECT * FROM licenses WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $license = $stmt->fetch(PDO::FETCH_ASSOC);
        ?>
        <div class="card">
            <div class="card-header"><h3>Edit License</h3></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="edit_license" value="1">
                    <input type="hidden" name="id" value="<?= $license['id'] ?>">
                    <div class="input-group"><label>License Key</label><input type="text" name="license_key" value="<?= htmlspecialchars($license['license_key']) ?>" required></div>
                    <div class="input-group"><label>Domain</label><input type="text" name="domain" value="<?= htmlspecialchars($license['domain']) ?>" required></div>
                    <div class="input-group"><label>Customer Email</label><input type="email" name="customer_email" value="<?= htmlspecialchars($license['customer_email']) ?>" required></div>
                    <div class="input-group"><label>Status</label><select name="status">
                        <option value="active" <?= $license['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $license['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select></div>
                    <button type="submit" class="btn">Save Changes</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h3>All Licenses</h3>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>License Key</th>
                            <th>Domain</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($licenses as $license): ?>
                            <tr>
                                <td><?= htmlspecialchars($license['license_key']) ?></td>
                                <td><?= htmlspecialchars($license['domain']) ?></td>
                                <td><?= htmlspecialchars($license['customer_email']) ?></td>
                                <td><?= htmlspecialchars($license['status']) ?></td>
                                <td class="action-links">
                                    <a href="licenses.php?action=edit&id=<?= $license['id'] ?>">Edit</a>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="toggle_status" value="1">
                                        <input type="hidden" name="id" value="<?= $license['id'] ?>">
                                        <input type="hidden" name="current_status" value="<?= $license['status'] ?>">
                                        <button type="submit" class="link-button"><?= $license['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <style>.input-group{margin-bottom:1rem} .link-button{background:none;border:none;color:#3b82f6;cursor:pointer;padding:0;font-size:1em;}</style>
    </div>
</body>
</html>
