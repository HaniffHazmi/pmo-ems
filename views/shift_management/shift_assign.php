<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

// Set timezone to Malaysia (GMT+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $eveningStaffId = $_POST['evening_staff'];
    $nightStaffId = $_POST['night_staff'];

    try {
        // Delete existing shifts for this date
        Shift::deleteByDate($date);

        // Assign evening shift if selected
        if (!empty($eveningStaffId)) {
            Shift::assignShift($eveningStaffId, 'evening', $date);
        }

        // Assign night shift if selected
        if (!empty($nightStaffId)) {
            Shift::assignShift($nightStaffId, 'night', $date);
        }

        $_SESSION['success'] = "Shifts assigned successfully!";
        header("Location: shift_monthly_index.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Error assigning shifts: " . $e->getMessage();
    }
}

include '../../views/partials/navbar.php';

$date = $_GET['date'] ?? date('Y-m-d');
$staff = Staff::getAll();

// Get existing shifts for the date
$existingShifts = Shift::getAllByDate($date);
$eveningStaffId = null;
$nightStaffId = null;

foreach ($existingShifts as $shift) {
    if ($shift['shift_type'] === 'evening') {
        $eveningStaffId = $shift['staff_id'];
    } elseif ($shift['shift_type'] === 'night') {
        $nightStaffId = $shift['staff_id'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assign Shifts - <?= htmlspecialchars($date) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/shift-management.css" />
</head>
<body>
<div class="shift-container">
    <div class="shift-header">
        <h1 class="shift-title">Assign Shifts</h1>
        <p class="shift-subtitle">Assign staff for <?= date('l, d M Y', strtotime($date)) ?></p>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="shift_assign.php">
            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
            
            <div class="mb-4">
                <label for="evening_staff" class="form-label">Evening Shift (4PM - 7PM)</label>
                <select id="evening_staff" name="evening_staff" class="form-control">
                    <option value="">Select staff...</option>
                    <?php foreach ($staff as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id'] == $eveningStaffId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-4">
                <label for="night_staff" class="form-label">Night Shift (8PM - 10PM)</label>
                <select id="night_staff" name="night_staff" class="form-control">
                    <option value="">Select staff...</option>
                    <?php foreach ($staff as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= $s['id'] == $nightStaffId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Assignments
                </button>
                <a href="shift_monthly_index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Schedule
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
