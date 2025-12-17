<?php
require_once('../db.php');

try {
    // Check if the customer_email column already exists in the licenses table
    $stmt = $pdo->query("SHOW COLUMNS FROM `licenses` LIKE 'customer_email'");
    $column_exists = $stmt->rowCount() > 0;

    // If the column does not exist, add it with a default value to avoid errors on existing rows
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `licenses` ADD COLUMN `customer_email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `domain`");
    }
} catch (PDOException $e) {
    // Log the error message to the server's error log
    // This prevents the migration script from breaking the admin dashboard if something goes wrong
    error_log("Database migration error in migrate.php: " . $e->getMessage());
}
?>
