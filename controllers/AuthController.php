<?php
session_start();
require_once '../config/database.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check admin first
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && $password === $admin['password']) {
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
        $_SESSION['user'] = $staff;
        $_SESSION['role'] = 'staff';
        header("Location: ../views/staff/staff_dashboard.php");
        exit;
    }

    $_SESSION['error'] = "Invalid email or password.";
    header("Location: ../views/login.php");
    exit;
}
