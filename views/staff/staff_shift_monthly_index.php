<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

// Ensure only staff can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: /views/login.php");
    exit;
}

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

// Fetch all shifts for the month
$allShifts = [];
for ($day = 1; $day <= $daysInMonth; $day++) {
    $date = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
    $shifts = Shift::getAllByDate($date);
    $allShifts[$date] = $shifts;
}

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
    <title>My Shift Schedule - <?= "$month/$year" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<?php include '../../views/partials/navbar.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">My Shift Schedule - <?= "$month/$year" ?></h2>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-auto">
            <input type="number" name="month" value="<?= $month ?>" min="1" max="12" class="form-control" />
        </div>
        <div class="col-auto">
            <input type="number" name="year" value="<?= $year ?>" min="2023" class="form-control" />
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">View</button>
        </div>
    </form>

    <table class="table table-bordered table-striped">
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
            <?php $no = 1; foreach ($allShifts as $date => $shifts): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('l, d M Y', strtotime($date)) ?></td>
                    <td><?= findStaffName($shifts, 'evening') ?? '<em>Not assigned</em>' ?></td>
                    <td><?= findStaffName($shifts, 'night') ?? '<em>Not assigned</em>' ?></td>
                    <td>
                        <?php
                        $eveningAssigned = findStaffName($shifts, 'evening');
                        $nightAssigned = findStaffName($shifts, 'night');

                        if (!$eveningAssigned || !$nightAssigned) {
                            echo '<a href="/views/staff/staff_shift_assign.php?date=' . $date . '" class="btn btn-sm btn-success">Assign</a>';
                        } else {
                            echo '<em>Full</em>';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
