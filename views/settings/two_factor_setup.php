<?php
session_start();
require_once '../../models/TwoFactorAuth.php';

if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}

$user = $_SESSION['user'];
$userType = $_SESSION['role'];
$userId = $user['id'];

// Get current 2FA status
$twoFactorStatus = TwoFactorAuth::getStatus($userId, $userType);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['setup'])) {
        $secret = TwoFactorAuth::generateSecret();
        if (TwoFactorAuth::setup($userId, $userType, $secret)) {
            $_SESSION['temp_2fa_secret'] = $secret;
            header('Location: verify_2fa_setup.php');
            exit;
        }
    } elseif (isset($_POST['disable'])) {
        if (TwoFactorAuth::disable($userId, $userType)) {
            $_SESSION['success'] = "Two-factor authentication has been disabled.";
            header('Location: two_factor_setup.php');
            exit;
        }
    }
}

$secret = $_SESSION['temp_2fa_secret'] ?? $twoFactorStatus['secret_key'] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Two-Factor Authentication Setup | PMO-EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Two-Factor Authentication</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?= $_SESSION['success'] ?>
                                <?php unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error'] ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($twoFactorStatus && $twoFactorStatus['is_enabled']): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-shield-alt"></i> Two-factor authentication is enabled for your account.
                            </div>
                            <form method="POST" class="mt-3">
                                <button type="submit" name="disable" class="btn btn-danger" 
                                        onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
                                    Disable Two-Factor Authentication
                                </button>
                            </form>
                        <?php else: ?>
                            <?php if ($secret): ?>
                                <div class="text-center mb-4">
                                    <h5>Add this code to your authenticator app</h5>
                                    <div class="alert alert-info">
                                        <code class="fs-4"><?= $secret ?></code>
                                    </div>
                                    <p class="text-muted">
                                        Enter this code manually in your authenticator app (Google Authenticator, Microsoft Authenticator, etc.)
                                    </p>
                                </div>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Please save your backup codes. You'll need them if you lose access to your authenticator app.
                                </div>
                            <?php else: ?>
                                <p>Two-factor authentication adds an extra layer of security to your account.</p>
                                <form method="POST">
                                    <button type="submit" name="setup" class="btn btn-primary">
                                        Enable Two-Factor Authentication
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 