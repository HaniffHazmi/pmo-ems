<?php
session_start();
require_once '../../models/TwoFactorAuth.php';

if (!isset($_SESSION['user']) || !isset($_SESSION['temp_2fa_secret'])) {
    header('Location: two_factor_setup.php');
    exit;
}

$user = $_SESSION['user'];
$userType = $_SESSION['role'];
$userId = $user['id'];
$secret = $_SESSION['temp_2fa_secret'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'] ?? '';
    
    if (TwoFactorAuth::verifyCode($secret, $code)) {
        if (TwoFactorAuth::enable($userId, $userType)) {
            $_SESSION['success'] = "Two-factor authentication has been enabled successfully!";
            unset($_SESSION['temp_2fa_secret']);
            header('Location: two_factor_setup.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Invalid verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Two-Factor Authentication | PMO-EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../partials/navbar.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Verify Two-Factor Authentication</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error'] ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <p>Please enter the verification code from your authenticator app to complete the setup.</p>
                        
                        <form method="POST" class="mt-4">
                            <div class="mb-3">
                                <label for="code" class="form-label">Verification Code</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       required autocomplete="off" pattern="[0-9]{6}" maxlength="6"
                                       placeholder="Enter 6-digit code">
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Verify and Enable</button>
                                <a href="two_factor_setup.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 