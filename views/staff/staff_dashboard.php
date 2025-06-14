<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';
require_once '../../models/Salary.php';

if (!isset($_SESSION['user'])) {
    header('Location: /views/login.php');
    exit;
}

// Set timezone to Malaysia (GMT+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

$staff = $_SESSION['user'];
$currentDate = date('Y-m-d');

// Get month and year from GET or current month/year
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Get all shifts for the staff in the selected month
$allShifts = Shift::getForMonth($staff['id'], $month);
$totalShifts = count($allShifts);

// Calculate future shifts and past shifts salary
$futureShifts = 0;
$pastShiftsSalary = 0;

foreach ($allShifts as $shift) {
    if (strtotime($shift['shift_date']) >= strtotime($currentDate)) {
        $futureShifts++;
    } else {
        // Calculate salary only for past shifts using rates from Salary model
        if ($shift['shift_type'] === 'evening') {
            $pastShiftsSalary += 12; // RM12 per evening shift
        } else if ($shift['shift_type'] === 'night') {
            $pastShiftsSalary += 8; // RM8 per night shift
        }
    }
}

// Format salary with 2 decimal places
$pastShiftsSalary = number_format($pastShiftsSalary, 2);

// Get month name
$monthName = date('F', mktime(0, 0, 0, $month, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/staff-dashboard.css" />
</head>
<body>
<?php include '../partials/navbar.php'; ?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Welcome, <?= htmlspecialchars($staff['name']) ?></h1>
        <p class="dashboard-subtitle">Here's your shift statistics and earnings overview for <?= $monthName ?> <?= $year ?></p>
    </div>

    <div class="month-year-selector mb-4">
        <form method="GET" class="d-flex gap-3 align-items-center">
            <div>
                <label for="month" class="form-label">Month</label>
                <select id="month" name="month" class="form-select" onchange="this.form.submit()">
                    <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label for="year" class="form-label">Year</label>
                <select id="year" name="year" class="form-select" onchange="this.form.submit()">
                    <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>
    </div>

    <div class="stats-grid">
        <!-- Total Shifts Card -->
        <div class="stat-card past">
            <div class="stat-header">
                <div class="stat-icon past">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="stat-title">Total Shifts</h3>
            </div>
            <div class="stat-value"><?= $totalShifts ?></div>
            <p class="stat-description">Total shifts for <?= $monthName ?> <?= $year ?></p>
        </div>

        <!-- Future Shifts Card -->
        <div class="stat-card future">
            <div class="stat-header">
                <div class="stat-icon future">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <h3 class="stat-title">Upcoming Shifts</h3>
            </div>
            <div class="stat-value"><?= $futureShifts ?></div>
            <p class="stat-description">Shifts remaining in <?= $monthName ?></p>
        </div>

        <!-- Past Shifts Salary Card -->
        <div class="stat-card salary">
            <div class="stat-header">
                <div class="stat-icon salary">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <h3 class="stat-title">Completed Shifts Earnings</h3>
            </div>
            <div class="stat-value">RM <?= $pastShiftsSalary ?></div>
            <p class="stat-description">Earnings from completed shifts in <?= $monthName ?></p>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="staff_shift_monthly_index.php?month=<?= $month ?>&year=<?= $year ?>" class="btn btn-primary">
            <i class="fas fa-calendar-alt"></i> View My Shift Schedule
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
