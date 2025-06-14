<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Staff.php';
require_once __DIR__ . '/../../models/Shift.php';

// Set timezone to Malaysia (GMT+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}

// Get total staff count
$totalStaff = Staff::getAll();
$totalStaffCount = count($totalStaff);

// Get today's assigned staff
$today = date('Y-m-d');
$todayShifts = Shift::getAllByDate($today);
$assignedStaffCount = count($todayShifts);

// Get staff names for today's shifts
$todayStaff = [];
foreach ($todayShifts as $shift) {
    $staff = Staff::getById($shift['staff_id']);
    if ($staff) {
        $todayStaff[] = [
            'name' => $staff['name'],
            'shift_type' => $shift['shift_type']
        ];
    }
}

// Get staff shift statistics for the current month
$currentMonth = date('m');
$staffStats = Staff::getShiftStatistics($currentMonth);

// Prepare data for the pie chart
$chartLabels = [];
$chartData = [];
$chartColors = [
    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
    '#5a5c69', '#858796', '#6f42c1', '#20c9a6', '#f8f9fc'
];

foreach ($staffStats as $index => $stat) {
    if ($stat['total_shifts'] > 0) {
        $chartLabels[] = $stat['name'];
        $chartData[] = $stat['total_shifts'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="/assets/css/admin-dashboard.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include __DIR__ . '/../partials/navbar.php'; ?>

  <div class="dashboard-container">
    <div class="welcome-message">
      <div class="welcome-content">
        <h1 class="dashboard-title">Welcome to Parcel Management System</h1>
        <p class="dashboard-subtitle">Group 4 Section 11</p>
        <div class="welcome-stats">
          <div class="welcome-stat-item">
            <i class="fas fa-box"></i>
            <span>Parcel Management</span>
          </div>
          <div class="welcome-stat-item">
            <i class="fas fa-truck"></i>
            <span>Delivery Tracking</span>
          </div>
          <div class="welcome-stat-item">
            <i class="fas fa-users"></i>
            <span>Staff Management</span>
          </div>
        </div>
      </div>
    </div>

    <div class="stats-container">
      <a href="/views/staff_management/staff_index.php" class="stat-card text-decoration-none">
        <div class="stat-icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="stat-title">Total Staff</div>
        <div class="stat-value"><?= $totalStaffCount ?></div>
      </a>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-title">Today's Assigned Staff</div>
        <div class="stat-value"><?= $assignedStaffCount ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-clock"></i>
        </div>
        <div class="stat-title">Today's Date</div>
        <div class="stat-value"><?= date('d M Y') ?></div>
      </div>

      <div class="stat-card">
        <div class="stat-icon">
          <i class="fas fa-chart-pie"></i>
        </div>
        <div class="stat-title">Staff Contribution</div>
        <div class="stat-value"><?= date('F Y') ?></div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-md-6">
        <div class="dashboard-header">
          <h2>Today's Staff Assignments</h2>
          <?php if (empty($todayStaff)): ?>
            <p>No staff assigned for today.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Staff Name</th>
                    <th>Shift Type</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($todayStaff as $staff): ?>
                    <tr>
                      <td><?= htmlspecialchars($staff['name']) ?></td>
                      <td><?= ucfirst($staff['shift_type']) ?></td>
                      <td>
                        <?php if ($staff['shift_type'] === 'evening'): ?>
                          4:00 PM - 7:00 PM
                        <?php else: ?>
                          8:00 PM - 10:00 PM
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="dashboard-header">
          <h2>Staff Contribution This Month</h2>
          <div class="chart-container" style="position: relative; height:300px;">
            <canvas id="contributionChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Initialize the pie chart
    const ctx = document.getElementById('contributionChart').getContext('2d');
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
          data: <?= json_encode($chartData) ?>,
          backgroundColor: <?= json_encode($chartColors) ?>,
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'right',
            labels: {
              font: {
                size: 12
              }
            }
          },
          title: {
            display: true,
            text: 'Total Shifts Completed',
            font: {
              size: 16
            }
          }
        }
      }
    });
  </script>
</body>
</html>
