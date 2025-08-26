<?php
// Database configuration for the license manager
define('DB_HOST', 'localhost');
define('DB_NAME', 'pmhmanager_license');
define('DB_USER', 'pmhmanager_license');
define('DB_PASS', 'pmhmanager_license');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create packages table (must exist before users table due to foreign key)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `packages` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `name` VARCHAR(255) NOT NULL,
      `price` DECIMAL(10, 2) NOT NULL,
      `max_licenses` INT NOT NULL,
      `features` TEXT
    )");

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `username` VARCHAR(255) NOT NULL UNIQUE,
      `email` VARCHAR(255) NOT NULL UNIQUE,
      `password` VARCHAR(255) NOT NULL,
      `api_key` VARCHAR(255) NOT NULL UNIQUE,
      `package_id` INT,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
    )");

    // Create licenses table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS `licenses` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `license_key` VARCHAR(255) NOT NULL UNIQUE,
      `domain` VARCHAR(255) NOT NULL,
      `user_id` INT,
      `package_id` INT,
      `status` ENUM('active', 'inactive') DEFAULT 'active',
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE SET NULL
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

    // Add default packages if none exist
    $stmt = $pdo->query("SELECT id FROM packages");
    if ($stmt->rowCount() == 0) {
        $packages = [
            ['name' => 'Basic', 'price' => 10.00, 'max_licenses' => 10, 'features' => '10 Licenses, 1 Website, Basic Support'],
            ['name' => 'Pro', 'price' => 50.00, 'max_licenses' => 100, 'features' => '100 Licenses, 10 Websites, Priority Support'],
            ['name' => 'Enterprise', 'price' => 100.00, 'max_licenses' => -1, 'features' => 'Unlimited Licenses, Unlimited Websites, 24/7 Support'] // -1 for unlimited
        ];
        $stmt = $pdo->prepare("INSERT INTO packages (name, price, max_licenses, features) VALUES (?, ?, ?, ?)");
        foreach ($packages as $pkg) {
            $stmt->execute([$pkg['name'], $pkg['price'], $pkg['max_licenses'], $pkg['features']]);
        }
    }

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
