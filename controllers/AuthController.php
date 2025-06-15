<?php
session_start();
require_once '../config/database.php';
require_once '../models/TwoFactorAuth.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check admin first
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && $password === $admin['password']) {
        // Check if 2FA is enabled
        $twoFactorStatus = TwoFactorAuth::getStatus($admin['id'], 'admin');
        
        if ($twoFactorStatus && $twoFactorStatus['is_enabled']) {
            // Store user info in session for 2FA verification
            $_SESSION['temp_user'] = $admin;
            $_SESSION['temp_role'] = 'admin';
            $_SESSION['temp_2fa_secret'] = $twoFactorStatus['secret_key'];
            header("Location: ../views/verify_2fa.php");
            exit;
        }

        // No 2FA, proceed with normal login
        $_SESSION['user'] = $admin;
        $_SESSION['role'] = 'admin';
        header("Location: ../views/admin/admin_dashboard.php");
        exit;
    }

    // Check staff
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
    $stmt->execute([$email]);
    $staff = $stmt->fetch();

    if ($staff && $password === $staff['password']) {
        // Check if 2FA is enabled
        $twoFactorStatus = TwoFactorAuth::getStatus($staff['id'], 'staff');
        
        if ($twoFactorStatus && $twoFactorStatus['is_enabled']) {
            // Store user info in session for 2FA verification
            $_SESSION['temp_user'] = $staff;
            $_SESSION['temp_role'] = 'staff';
            $_SESSION['temp_2fa_secret'] = $twoFactorStatus['secret_key'];
            header("Location: ../views/verify_2fa.php");
            exit;
        }

        // No 2FA, proceed with normal login
        $_SESSION['user'] = $staff;
        $_SESSION['role'] = 'staff';
        header("Location: ../views/staff/staff_dashboard.php");
        exit;
    }

    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../views/login.php");
    exit;
}
