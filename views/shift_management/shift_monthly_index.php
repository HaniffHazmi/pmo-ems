<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

include '../../views/partials/navbar.php';  // Include navbar

// Set timezone to Malaysia (GMT+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

// Get month from GET or current month
$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Get current date in Kuala Lumpur timezone
$currentDate = date('Y-m-d');

// Get number of days in the month
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Prepare arrays to hold shifts per date
$futureShifts = [];
$pastShifts = [];

for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
    $shifts = Shift::getAllByDate($date);

    // Assign empty by default
    $shiftData = [
        'evening' => null,
        'night' => null
    ];

    foreach ($shifts as $shift) {
        if ($shift['shift_type'] === 'evening') {
            $shiftData['evening'] = $shift['staff_id'];
        } elseif ($shift['shift_type'] === 'night') {
            $shiftData['night'] = $shift['staff_id'];
        }
    }

    if (strtotime($date) < strtotime($currentDate)) {
        $pastShifts[$date] = $shiftData;
    } else {
        $futureShifts[$date] = $shiftData;
    }
}

// Sort future shifts by date (ascending)
ksort($futureShifts);

// Sort past shifts by date (descending)
krsort($pastShifts);

// Combine future and past shifts
$shiftData = $futureShifts + $pastShifts;

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Monthly Shift Schedule - <?= htmlspecialchars("$year-$month") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/shift-management.css" />
</head>
<body>
<div class="shift-container">
    <div class="shift-header">
        <h1 class="shift-title">Monthly Shift Schedule</h1>
        <p class="shift-subtitle">Manage staff shifts for <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></p>
    </div>

    <div class="month-year-selector">
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

    <div class="filter-container mb-4">
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" id="dayFilter" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-calendar-day"></i> Filter by Day
            </button>
            <ul class="dropdown-menu" aria-labelledby="dayFilter">
                <li><a class="dropdown-item" href="#" data-day="all">All Days</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="#" data-day="0">Sunday</a></li>
                <li><a class="dropdown-item" href="#" data-day="1">Monday</a></li>
                <li><a class="dropdown-item" href="#" data-day="2">Tuesday</a></li>
                <li><a class="dropdown-item" href="#" data-day="3">Wednesday</a></li>
                <li><a class="dropdown-item" href="#" data-day="4">Thursday</a></li>
                <li><a class="dropdown-item" href="#" data-day="5">Friday</a></li>
                <li><a class="dropdown-item" href="#" data-day="6">Saturday</a></li>
            </ul>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table" id="shiftsTable">
            <thead>
            <tr>
                <th>No.</th>
                <th>Day</th>
                <th>Date</th>
                <th>Evening (4PM - 7PM)</th>
                <th>Night (8PM - 10PM)</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $count = 1;
            $isFirstPastDate = true;
            foreach ($shiftData as $date => $shifts):
                $timestamp = strtotime($date);
                $dayNumber = date('w', $timestamp);
                $isPastDate = strtotime($date) < strtotime($currentDate);

                // Add separator row before first past date
                if ($isPastDate && $isFirstPastDate) {
                    $isFirstPastDate = false;
                    echo '<tr class="table-separator">
                            <td colspan="6">
                                <div class="separator">
                                    <span>Past Dates</span>
                                </div>
                            </td>
                          </tr>';
                }
            ?>
                <tr data-day="<?= $dayNumber ?>" class="<?= $isPastDate ? 'past-date' : '' ?>">
                    <td><?= $count++ ?></td>
                    <td><?= date('l', $timestamp) ?></td>
                    <td><?= date('d M Y', $timestamp) ?></td>
                    <td>
                        <?php if ($shifts['evening']): ?>
                            <span class="badge badge-evening"><?= getStaffName($shifts['evening']) ?></span>
                        <?php else: ?>
                            <em>Not assigned</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($shifts['night']): ?>
                            <span class="badge badge-night"><?= getStaffName($shifts['night']) ?></span>
                        <?php else: ?>
                            <em>Not assigned</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$isPastDate): ?>
                            <div class="action-buttons">
                                <?php if (!$shifts['evening'] || !$shifts['night']): ?>
                                    <a href="shift_assign.php?date=<?= $date ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-user-plus"></i> Assign
                                    </a>
                                <?php endif; ?>
                                <?php if ($shifts['evening']): ?>
                                    <a href="shift_update.php?date=<?= $date ?>&shift_type=evening" class="btn btn-warning btn-sm">
                                        <i class="fas fa-user-edit"></i> Update Evening
                                    </a>
                                <?php endif; ?>
                                <?php if ($shifts['night']): ?>
                                    <a href="shift_update.php?date=<?= $date ?>&shift_type=night" class="btn btn-warning btn-sm">
                                        <i class="fas fa-user-edit"></i> Update Night
                                    </a>
                                <?php endif; ?>
                                <a href="shift_delete.php?date=<?= $date ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('Are you sure you want to delete this shift?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        <?php else: ?>
                            <span class="text-muted"><i class="fas fa-lock"></i> Past date</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('shiftsTable');
    const rows = table.querySelectorAll('tbody tr:not(.table-separator)');
    let currentDay = 'all';

    // Add click handlers to dropdown items
    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const day = e.target.dataset.day;
            filterByDay(day);
            
            // Update button text
            const button = document.getElementById('dayFilter');
            if (day === 'all') {
                button.innerHTML = '<i class="fas fa-calendar-day"></i> Filter by Day';
            } else {
                const dayName = e.target.textContent;
                button.innerHTML = `<i class="fas fa-calendar-day"></i> ${dayName}`;
            }
        });
    });

    function filterByDay(day) {
        let visibleCount = 0;
        rows.forEach(row => {
            if (day === 'all' || row.dataset.day === day) {
                row.style.display = '';
                visibleCount++;
                // Update row numbers
                row.querySelector('td:first-child').textContent = visibleCount;
            } else {
                row.style.display = 'none';
            }
        });
    }
});
</script>
</body>
</html>
