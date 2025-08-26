<?php
$page_title = 'Installation - Step 2: Setup';
$errors = [];

// Define the path for the new config file
$config_file_path = '../src/config.php';
$db_file_path = '../src/db.php';

// If config file already exists, it means installation is likely complete.
// For security, we should not be able to run setup again.
if (file_exists($config_file_path)) {
    $errors[] = 'Configuration file already exists. Please delete `install/` directory for security.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errors)) {
    // 1. Collect and validate form data
    $db_host = trim($_POST['db_host']);
    $db_name = trim($_POST['db_name']);
    $db_user = trim($_POST['db_user']);
    $db_pass = trim($_POST['db_pass']);

    $admin_user = trim($_POST['admin_user']);
    $admin_email = trim($_POST['admin_email']);
    $admin_pass = trim($_POST['admin_pass']);

    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($admin_user) || empty($admin_email) || empty($admin_pass)) {
        $errors[] = 'All fields are required.';
    }

    // 2. Test database connection
    if (empty($errors)) {
        try {
            $test_pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $test_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        }
    }

    // 3. Write config file and run installation
    if (empty($errors)) {
        // Create config file content
        $config_content = "<?php\n";
        $config_content .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
        $config_content .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
        $config_content .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
        $config_content .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";

        // Write the config file
        if (!file_put_contents($config_file_path, $config_content)) {
            $errors[] = 'Could not write config file. Please check permissions on `src/`.';
        } else {
            try {
                // Modify db.php to use the new config file
                $db_content = file_get_contents($db_file_path);
                $db_content = preg_replace(
                    "/define\('DB_HOST'.*?;/s",
                    "require_once __DIR__ . '/config.php';",
                    $db_content, 1
                );
                // Remove other define statements
                $db_content = preg_replace("/define\('DB_NAME'.*?;/s", "", $db_content);
                $db_content = preg_replace("/define\('DB_USER'.*?;/s", "", $db_content);
                $db_content = preg_replace("/define\('DB_PASS'.*?;/s", "", $db_content);
                file_put_contents($db_file_path, $db_content);

                // Now, include the modified db.php to run the schema setup
                require_once $db_file_path;

                // Overwrite the default admin user with the new one
                $password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
                // Delete old 'admin' user if exists
                $pdo->exec("DELETE FROM admins WHERE username = 'admin'");
                // Insert new admin user
                $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
                $stmt->execute([$admin_user, $password_hash]);

                // Also add admin email to settings
                $settings_file = '../src/settings.json';
                $settings = [];
                if (file_exists($settings_file)) {
                    $settings = json_decode(file_get_contents($settings_file), true);
                }
                $settings['admin_email'] = $admin_email;
                file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

                // Redirect to success page
                session_start();
                $_SESSION['install_success'] = true;
                $_SESSION['admin_user'] = $admin_user;
                $_SESSION['admin_pass'] = $admin_pass;
                header('Location: success.php');
                exit();

            } catch (Exception $e) {
                $errors[] = 'An error occurred during installation: ' . $e->getMessage();
                // Clean up by deleting the config file if something went wrong
                unlink($config_file_path);
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
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="h3 m-2 text-center">License Manager Installation</h1>
                    </div>
                    <div class="card-body p-4">
                        <h4 class="mb-4">Step 2: Database & Admin Details</h4>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <h5>Database Details</h5>
                            <hr>
                            <div class="mb-3"><label for="db_host" class="form-label">Database Host</label><input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required></div>
                            <div class="mb-3"><label for="db_name" class="form-label">Database Name</label><input type="text" class="form-control" id="db_name" name="db_name" required></div>
                            <div class="mb-3"><label for="db_user" class="form-label">Database User</label><input type="text" class="form-control" id="db_user" name="db_user" required></div>
                            <div class="mb-3"><label for="db_pass" class="form-label">Database Password</label><input type="password" class="form-control" id="db_pass" name="db_pass"></div>

                            <h5 class="mt-5">Admin Account</h5>
                            <hr>
                            <div class="mb-3"><label for="admin_user" class="form-label">Admin Username</label><input type="text" class="form-control" id="admin_user" name="admin_user" value="admin" required></div>
                            <div class="mb-3"><label for="admin_email" class="form-label">Admin Email</label><input type="email" class="form-control" id="admin_email" name="admin_email" required></div>
                            <div class="mb-3"><label for="admin_pass" class="form-label">Admin Password</label><input type="text" class="form-control" id="admin_pass" name="admin_pass" required></div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary" <?= file_exists($config_file_path) ? 'disabled' : '' ?>>Complete Installation</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
