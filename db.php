<?php
// Database configuration for the license manager
define('DB_HOST', 'localhost');
define('DB_NAME', 'pmhmanager_license');
define('DB_USER', 'pmhmanager_license');
define('DB_PASS', 'pmhmanager_license');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create licenses table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS `licenses` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `license_key` VARCHAR(255) NOT NULL UNIQUE,
      `domain` VARCHAR(255) NOT NULL,
      `customer_email` VARCHAR(255) NOT NULL,
      `status` ENUM('active', 'inactive') DEFAULT 'active',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Create admins table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `username` VARCHAR(255) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL
    )");

    // Create transactions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `transactions` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `license_id` INT,
      `transaction_ref` VARCHAR(255) NOT NULL,
      `amount` DECIMAL(10, 2) NOT NULL,
      `currency` VARCHAR(3) NOT NULL,
      `status` VARCHAR(50) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (license_id) REFERENCES licenses(id) ON DELETE SET NULL
    )");

    // Add default admin user if not exists
    $stmt = $pdo->query("SELECT id FROM admins WHERE username = 'admin'");
    if ($stmt->rowCount() == 0) {
        $admin_pass_hash = password_hash('password', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)")->execute(['admin', $admin_pass_hash]);
    }

} catch (PDOException $e) {
    // In a real app, you'd want to log this error and show a generic error page.
    die("Database connection failed: " . $e->getMessage());
}
?>
