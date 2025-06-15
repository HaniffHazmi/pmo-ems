<?php
// config/database.php

$host = 'localhost';
$db   = 'pmo_ems';
$user = 'root';
$pass = ''; // change if needed

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Create 2FA table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS two_factor_auth (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('admin', 'staff') NOT NULL,
        secret_key VARCHAR(32) NOT NULL,
        backup_codes TEXT,
        is_enabled BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id, user_type)
    )");
} catch (PDOException $e) {
    die("Error creating 2FA table: " . $e->getMessage());
}
