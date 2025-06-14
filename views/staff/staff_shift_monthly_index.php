<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

// Ensure only staff can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: /views/login.php");
    exit;
}

// Get staff information from session
$staff = $_SESSION['user'];

// Set timezone to Malaysia (GMT+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Get current date in Kuala Lumpur timezone
$currentDate = date('Y-m-d');

// Fetch all shifts for the month
$allShifts = [];
$futureShifts = [];
$pastShifts = [];

for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    $shifts = Shift::getAllByDate($date);
    
    if (strtotime($date) < strtotime($currentDate)) {
        $pastShifts[$date] = $shifts;
    } else {
        $futureShifts[$date] = $shifts;
    }
}

// Sort future shifts by date (ascending)
ksort($futureShifts);

// Sort past shifts by date (descending)
krsort($pastShifts);

// Combine future and past shifts
$allShifts = $futureShifts + $pastShifts;

function findStaffName($shifts, $type) {
    foreach ($shifts as $shift) {
        if ($shift['shift_type'] === $type) {
            $staff = Staff::getById($shift['staff_id']);
            return $staff['name'] ?? '';
        }
    }
    return null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Shift Schedule - <?= "$month/$year" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/staff-shift-management.css" />
</head>
<body>
<?php include '../../views/partials/navbar.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>My Shift Schedule - <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <form method="GET" class="month-year-selector">
                <div class="form-group">
                    <label for="month" class="form-label">Month</label>
                    <select id="month" name="month" class="form-select" onchange="this.form.submit()">
                        <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="year" class="form-label">Year</label>
                    <select id="year" name="year" class="form-select" onchange="this.form.submit()">
                        <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>

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
                        <th>Date</th>
                        <th>Evening (4PM - 7PM)</th>
                        <th>Night (8PM - 10PM)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    $isFirstPastDate = true;
                    foreach ($allShifts as $date => $shifts): 
                        $dayNumber = date('w', strtotime($date));
                        $isPastDate = strtotime($date) < strtotime($currentDate);
                        
                        // Add separator row before first past date
                        if ($isPastDate && $isFirstPastDate) {
                            $isFirstPastDate = false;
                            echo '<tr class="table-separator">
                                    <td colspan="5">
                                        <div class="separator">
                                            <span>Past Dates</span>
                                        </div>
                                    </td>
                                  </tr>';
                        }
                    ?>
                        <tr data-day="<?= $dayNumber ?>" class="<?= $isPastDate ? 'past-date' : '' ?>">
                            <td data-label="No."><?= $no++ ?></td>
                            <td data-label="Date"><?= date('l, d M Y', strtotime($date)) ?></td>
                            <td data-label="Evening Shift">
                                <?php 
                                $eveningStaff = findStaffName($shifts, 'evening');
                                if ($eveningStaff): ?>
                                    <span class="badge badge-evening"><?= htmlspecialchars($eveningStaff) ?></span>
                                <?php else: ?>
                                    <em>Not assigned</em>
                                <?php endif; ?>
                            </td>
                            <td data-label="Night Shift">
                                <?php 
                                $nightStaff = findStaffName($shifts, 'night');
                                if ($nightStaff): ?>
                                    <span class="badge badge-night"><?= htmlspecialchars($nightStaff) ?></span>
                                <?php else: ?>
                                    <em>Not assigned</em>
                                <?php endif; ?>
                            </td>
                            <td data-label="Action">
                                <?php
                                $eveningAssigned = findStaffName($shifts, 'evening');
                                $nightAssigned = findStaffName($shifts, 'night');

                                if ($isPastDate) {
                                    echo '<span class="text-muted"><i class="fas fa-lock"></i> Past date</span>';
                                } else {
                                    if ($eveningAssigned === $staff['name']) {
                                        echo '<a href="staff_shift_cancel.php?date=' . $date . '&shift_type=evening" class="btn btn-danger btn-sm me-2" onclick="return confirm(\'Are you sure you want to cancel this shift?\')">
                                            <i class="fas fa-times"></i> Cancel Evening
                                        </a>';
                                    } elseif ($nightAssigned === $staff['name']) {
                                        echo '<a href="staff_shift_cancel.php?date=' . $date . '&shift_type=night" class="btn btn-danger btn-sm me-2" onclick="return confirm(\'Are you sure you want to cancel this shift?\')">
                                            <i class="fas fa-times"></i> Cancel Night
                                        </a>';
                                    } elseif (!$eveningAssigned || !$nightAssigned) {
                                        echo '<a href="staff_shift_assign.php?date=' . $date . '" class="btn btn-success btn-sm">
                                            <i class="fas fa-user-plus"></i> Assign
                                        </a>';
                                    } else {
                                        echo '<span class="text-muted"><i class="fas fa-check-circle"></i> Full</span>';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
