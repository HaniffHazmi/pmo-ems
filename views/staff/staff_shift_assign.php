<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: /views/login.php");
    exit;
}

$staff = $_SESSION['user'];
$date = $_GET['date'] ?? date('Y-m-d');

// Redirect if date is missing
if (!$date) {
    header("Location: staff_shift_monthly_index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shift_type = $_POST['shift_type'] ?? '';

    // Check if the shift type is still available
    $existingShifts = Shift::getAllByDate($date);
    $alreadyAssigned = false;
    foreach ($existingShifts as $s) {
        if ($s['shift_type'] === $shift_type) {
            $alreadyAssigned = true;
            break;
        }
    }

    if (!$alreadyAssigned) {
        Shift::assignShift($staff['id'], $shift_type, $date);
        header("Location: staff_shift_monthly_index.php?month=" . date('m', strtotime($date)) . "&year=" . date('Y', strtotime($date)));
        exit;
    } else {
        $error = "That shift slot has already been assigned to another staff.";
    }
}

// Determine available shifts
$shiftsToday = Shift::getAllByDate($date);
$eveningTaken = false;
$nightTaken = false;

foreach ($shiftsToday as $shift) {
    if ($shift['shift_type'] === 'evening') $eveningTaken = true;
    if ($shift['shift_type'] === 'night') $nightTaken = true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Shift - <?= htmlspecialchars($date) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/staff-shift-management.css" />
</head>
<body>
<?php include '../partials/navbar.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>Assign Yourself to a Shift</h2>
        <p class="text-muted mb-4"><?= date('l, d M Y', strtotime($date)) ?></p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>"/>

            <div class="mb-4">
                <label class="form-label">Select Available Shift:</label>

                <?php if (!$eveningTaken): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="shift_type" id="evening" value="evening" required>
                        <label class="form-check-label" for="evening">
                            <i class="fas fa-sun"></i> Evening Shift (4PM - 7PM)
                        </label>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Evening shift is already taken.
                    </div>
                <?php endif; ?>

                <?php if (!$nightTaken): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="shift_type" id="night" value="night" required>
                        <label class="form-check-label" for="night">
                            <i class="fas fa-moon"></i> Night Shift (8PM - 10PM)
                        </label>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Night shift is already taken.
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex gap-3">
                <?php if (!$eveningTaken || !$nightTaken): ?>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Assign Myself
                    </button>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> All shifts for this date are already filled.
                    </div>
                <?php endif; ?>
                <a href="staff_shift_monthly_index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Schedule
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
