<?php
$role = $_SESSION['role'] ?? null;
$user = $_SESSION['user'] ?? null;
?>

<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <i class="fas fa-box-open me-2"></i>
      PMO-EMS
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if ($role === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="/views/admin/admin_dashboard.php">
              <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/staff_management/staff_index.php">
              <i class="fas fa-users me-1"></i> Manage Staff
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/shift_management/shift_monthly_index.php">
              <i class="fas fa-calendar-alt me-1"></i> Manage Shifts
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/salary/manage_salary.php">
              <i class="fas fa-money-bill-wave me-1"></i> Manage Salary
            </a>
          </li>
        <?php elseif ($role === 'staff'): ?>
          <li class="nav-item">
            <a class="nav-link" href="/views/staff/staff_dashboard.php">
              <i class="fas fa-tachometer-alt me-1"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/staff/staff_shift_monthly_index.php">
              <i class="fas fa-calendar-alt me-1"></i> Manage Shift
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if ($user): ?>
          <li class="nav-item">
            <span class="navbar-text">
              <i class="fas fa-user-circle me-1"></i>
              Hello, <?= htmlspecialchars($user['name']) ?>
            </span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/controllers/logout.php" onclick="return confirm('Are you sure you want to logout?');">
              <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/views/login.php">
              <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<link rel="stylesheet" href="/assets/css/navbar.css">
