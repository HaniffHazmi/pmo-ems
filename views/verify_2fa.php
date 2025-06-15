<?php
session_start();
require_once '../models/TwoFactorAuth.php';

if (!isset($_SESSION['temp_user']) || !isset($_SESSION['temp_2fa_secret'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    $backupCode = $_POST['backup_code'] ?? '';
    
    if (!empty($backupCode)) {
        // Verify backup code
        if (TwoFactorAuth::verifyBackupCode($_SESSION['temp_user']['id'], $_SESSION['temp_role'], $backupCode)) {
            // Backup code is valid, complete login
            $_SESSION['user'] = $_SESSION['temp_user'];
            $_SESSION['role'] = $_SESSION['temp_role'];
            
            // Clean up temporary session data
            unset($_SESSION['temp_user']);
            unset($_SESSION['temp_role']);
            unset($_SESSION['temp_2fa_secret']);
            
            // Redirect to appropriate dashboard
            $redirect = $_SESSION['role'] === 'admin' ? 'admin/admin_dashboard.php' : 'staff/staff_dashboard.php';
            header("Location: $redirect");
            exit;
        } else {
            $_SESSION['error'] = "Invalid backup code.";
        }
    } else if (!empty($code)) {
        // Verify TOTP code
        if (TwoFactorAuth::verifyCode($_SESSION['temp_2fa_secret'], $code)) {
            // Code is valid, complete login
            $_SESSION['user'] = $_SESSION['temp_user'];
            $_SESSION['role'] = $_SESSION['temp_role'];
            
            // Clean up temporary session data
            unset($_SESSION['temp_user']);
            unset($_SESSION['temp_role']);
            unset($_SESSION['temp_2fa_secret']);
            
            // Redirect to appropriate dashboard
            $redirect = $_SESSION['role'] === 'admin' ? 'admin/admin_dashboard.php' : 'staff/staff_dashboard.php';
            header("Location: $redirect");
            exit;
        } else {
            $_SESSION['error'] = "Invalid verification code. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Two-Factor Authentication | PMO-EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
            <h3 class="text-center mb-4">Two-Factor Authentication</h3>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>How to get your code:</strong>
                    <ol class="mb-0 mt-2">
                        <li>Open your authenticator app</li>
                        <li>Look for "PMO-EMS" or your email</li>
                        <li>Enter the 6-digit code shown</li>
                    </ol>
                </div>
            </div>

            <form method="POST">
                <div class="mb-3">
                    <label for="code" class="form-label">Verification Code</label>
                    <input type="text" class="form-control" id="code" name="code" 
                           required autocomplete="off" pattern="[0-9]{6}" maxlength="6"
                           placeholder="Enter 6-digit code">
                    <div class="form-text">The code changes every 30 seconds</div>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Verify</button>
                </div>

                <div class="text-center">
                    <p class="text-muted">Lost access to your authenticator app?</p>
                    <button type="button" class="btn btn-link" data-bs-toggle="collapse" 
                            data-bs-target="#backupCodeForm">
                        Use a backup code
                    </button>
                </div>

                <div class="collapse mt-3" id="backupCodeForm">
                    <div class="card card-body">
                        <div class="mb-3">
                            <label for="backup_code" class="form-label">Backup Code</label>
                            <input type="text" class="form-control" id="backup_code" name="backup_code" 
                                   placeholder="Enter your backup code">
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">Use Backup Code</button>
                    </div>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 