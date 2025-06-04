<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

include '../../views/partials/navbar.php';  // Include navbar

// Get month from GET or current month
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Get number of days in the month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Prepare array to hold shifts per date
$shiftData = [];

for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $shifts = Shift::getAllByDate($date);

    // Assign empty by default
    $shiftData[$date] = [
        'evening' => null,
        'night' => null
    ];

    foreach ($shifts as $shift) {
        if ($shift['shift_type'] === 'evening') {
            $shiftData[$date]['evening'] = $shift['staff_id'];
        } elseif ($shift['shift_type'] === 'night') {
            $shiftData[$date]['night'] = $shift['staff_id'];
        }
    }
}

// Helper to get staff name by ID
function getStaffName($id) {
    if (!$id) return '<em>Not assigned</em>';
    $staff = Staff::getById($id);
    return htmlspecialchars($staff['name']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Monthly Shift Schedule - <?= htmlspecialchars("$year-$month") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container mt-4">
    <h2>Monthly Shift Schedule for <?= htmlspecialchars("$year-$month") ?></h2>

    <form method="GET" class="mb-3 row g-2 align-items-center">
        <div class="col-auto">
            <label for="month" class="form-label">Month</label>
            <select id="month" name="month" class="form-select" onchange="this.form.submit()">
                <?php for ($m=1; $m<=12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <label for="year" class="form-label">Year</label>
            <select id="year" name="year" class="form-select" onchange="this.form.submit()">
                <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </form>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>No.</th>
            <th>Date</th>
            <th>Evening (4PM - 7PM)</th>
            <th>Night (8PM - 10PM)</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $count = 1;
        foreach ($shiftData as $date => $shifts):
            ?>
            <tr>
                <td><?= $count++ ?></td>
                <td><?= htmlspecialchars($date) ?></td>
                <td><?= getStaffName($shifts['evening']) ?></td>
                <td><?= getStaffName($shifts['night']) ?></td>
                <td>
                    <a href="shift_assign.php?date=<?= $date ?>" class="btn btn-sm btn-primary">Assign</a>
                    <a href="shift_delete.php?date=<?= $date ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Are you sure you want to delete all shifts on <?= $date ?>?')">Delete</a>
                </td>

            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
