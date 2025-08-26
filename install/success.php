<?php
session_start();

// Security check: Only allow access if installation was just successfully completed.
if (!isset($_SESSION['install_success']) || $_SESSION['install_success'] !== true) {
    // Redirect to the first step if not.
    header('Location: index.php');
    exit();
}

$admin_user = $_SESSION['admin_user'];
$admin_pass = $_SESSION['admin_pass'];

// Unset session variables to prevent reuse
unset($_SESSION['install_success']);
unset($_SESSION['admin_user']);
unset($_SESSION['admin_pass']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="h3 m-2 text-center">Installation Successful!</h1>
                    </div>
                    <div class="card-body p-4 text-center">
                        <p class="fs-5">The License Manager has been installed successfully.</p>

                        <div class="alert alert-info">
                            <h5 class="alert-heading">Your Admin Login Details</h5>
                            <p class="mb-1"><strong>Username:</strong> <?= htmlspecialchars($admin_user) ?></p>
                            <p class="mb-0"><strong>Password:</strong> <?= htmlspecialchars($admin_pass) ?> <em>(This is the only time it will be shown)</em></p>
                        </div>

                        <div class="alert alert-danger mt-4">
                            <h4 class="alert-heading">!! IMPORTANT SECURITY WARNING !!</h4>
                            <p>For the security of your application, please **DELETE the entire `install/` directory** from your server immediately.</p>
                            <p class="mb-0">Leaving the installer on a live server is a major security risk.</p>
                        </div>

                        <div class="d-grid mt-4">
                           <a href="../public/admin/" class="btn btn-primary btn-lg">Go to Admin Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
