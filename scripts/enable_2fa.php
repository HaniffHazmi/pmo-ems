<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TwoFactorAuth.php';

// Get all staff members
$stmt = $pdo->query("SELECT * FROM staff");
$staffMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$enabledCount = 0;
$errors = [];

foreach ($staffMembers as $staff) {
    try {
        // Generate a secret for each staff member
        $secret = TwoFactorAuth::generateSecret();
        
        // Setup 2FA
        if (TwoFactorAuth::setup($staff['id'], 'staff', $secret)) {
            // Enable 2FA
            if (TwoFactorAuth::enable($staff['id'], 'staff')) {
                $enabledCount++;
                echo "Enabled 2FA for staff: {$staff['email']}\n";
                echo "Secret: $secret\n";
                echo "----------------------------------------\n";
            } else {
                $errors[] = "Failed to enable 2FA for {$staff['email']}";
            }
        } else {
            $errors[] = "Failed to setup 2FA for {$staff['email']}";
        }
    } catch (Exception $e) {
        $errors[] = "Error processing {$staff['email']}: " . $e->getMessage();
    }
}

echo "\nSummary:\n";
echo "Successfully enabled 2FA for $enabledCount staff members\n";
if (!empty($errors)) {
    echo "\nErrors:\n";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
?> 