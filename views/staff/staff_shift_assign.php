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
    <title>Assign Shift - <?= htmlspecialchars($date) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<?php include '../partials/navbar.php'; ?>

<div class="container mt-4">
    <h3>Assign Yourself to a Shift - <?= date('l, d M Y', strtotime($date)) ?></h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>"/>

        <div class="mb-3">
            <label class="form-label">Select Available Shift:</label><br/>

            <?php if (!$eveningTaken): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="shift_type" id="evening" value="evening" required>
                    <label class="form-check-label" for="evening">Evening (4PM - 7PM)</label>
                </div>
            <?php else: ?>
                <div class="text-muted">Evening shift is already taken.</div>
            <?php endif; ?>

            <?php if (!$nightTaken): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="shift_type" id="night" value="night" required>
                    <label class="form-check-label" for="night">Night (8PM - 10PM)</label>
                </div>
            <?php else: ?>
                <div class="text-muted">Night shift is already taken.</div>
            <?php endif; ?>
        </div>

        <?php if (!$eveningTaken || !$nightTaken): ?>
            <button type="submit" class="btn btn-success">Assign Myself</button>
        <?php else: ?>
            <div class="alert alert-info">All shifts for this date are already filled.</div>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
