<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['domain'])) {
    $domain = trim($_POST['domain']);
    $user_id = $_SESSION['user_id'];

    if (empty($domain)) {
        $_SESSION['error_message'] = 'Domain name is required.';
        header('Location: dashboard.php');
        exit();
    }

    // Get user's package and current license count
    $stmt = $pdo->prepare("
        SELECT p.max_licenses, u.package_id
        FROM users u
        JOIN packages p ON u.package_id = p.id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user_package_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user_package_info) {
        $_SESSION['error_message'] = 'You do not have an active package. Please purchase one first.';
        header('Location: dashboard.php');
        exit();
    }

    $max_licenses = $user_package_info['max_licenses'];
    $package_id = $user_package_info['package_id'];

    // Count current licenses
    $stmt = $pdo->prepare("SELECT count(*) FROM licenses WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $current_license_count = $stmt->fetchColumn();

    // Check if user can create a new license
    if ($max_licenses != -1 && $current_license_count >= $max_licenses) {
        $_SESSION['error_message'] = 'You have reached your license limit. Please upgrade your package to create more licenses.';
        header('Location: dashboard.php');
        exit();
    }

    // All checks passed, create the new license
    try {
        $license_key = 'LIC-' . strtoupper(bin2hex(random_bytes(10)));

        $stmt = $pdo->prepare(
            "INSERT INTO licenses (license_key, domain, user_id, package_id, status) VALUES (?, ?, ?, ?, 'active')"
        );
        $stmt->execute([$license_key, $domain, $user_id, $package_id]);

        $_SESSION['success_message'] = 'License created successfully for ' . htmlspecialchars($domain);

    } catch (PDOException $e) {
        // In a real app, log this error
        $_SESSION['error_message'] = 'Could not create license due to a database error.';
    }

} else {
    $_SESSION['error_message'] = 'Invalid request.';
}

header('Location: dashboard.php');
exit();
?>
