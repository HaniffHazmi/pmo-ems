<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="container mt-4">
    <h1>Welcome to your dashboard</h1>
    <!-- Your dashboard content here -->
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
