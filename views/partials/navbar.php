<?php
$role = $_SESSION['role'] ?? null;
$user = $_SESSION['user'] ?? null;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">PMO-EMS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">

        <?php if ($role === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link" href="/views/admin/admin_dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/admin/manage_staff.php">Manage Staff</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/admin/manage_shift.php">Manage Shifts</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/admin/manage_salary.php">Manage Salary</a>
          </li>
        <?php elseif ($role === 'staff'): ?>
          <li class="nav-item">
            <a class="nav-link" href="/views/staff/staff_dashboard.php">Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/views/staff/manage_shift.php">Manage Shift</a>
          </li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <?php if ($user): ?>
          <li class="nav-item">
            <span class="navbar-text me-3">Hello, <?= htmlspecialchars($user['name']) ?></span>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/controllers/logout.php">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/views/login.php">Login</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
