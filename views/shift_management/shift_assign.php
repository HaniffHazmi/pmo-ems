<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

include '../../views/partials/navbar.php';

$date = $_GET['date'] ?? date('Y-m-d');

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = $_POST['staff_id'] ?? null;
    $shift_type = $_POST['shift_type'] ?? null;

    if (!$staff_id || !$shift_type) {
        $error = 'Please select both staff and shift type.';
    } else {
        $assigned = Shift::assignShift($staff_id, $shift_type, $date);
        if ($assigned) {
            $success = true;
        } else {
            $error = 'Failed to assign shift. It may already be taken.';
        }
    }
}

$allStaff = Staff::getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Assign Shift - <?= htmlspecialchars($date) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body>
<div class="container mt-4">
    <h2>Assign Shift for <?= htmlspecialchars($date) ?></h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Shift assigned successfully!</div>
        <a href="shift_monthly_index.php" class="btn btn-secondary">Back to Monthly View</a>
    <?php else: ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-3">
            <div class="mb-3">
                <label for="staff_id" class="form-label">Select Staff:</label>
                <select name="staff_id" id="staff_id" class="form-select" required>
                    <option value="">-- Choose Staff --</option>
                    <?php foreach ($allStaff as $staff): ?>
                        <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Shift Type:</label><br/>
                <div class="form-check form-check-inline">
                    <input type="radio" name="shift_type" value="evening" id="evening" class="form-check-input" required>
                    <label for="evening" class="form-check-label">Evening (4PM - 7PM)</label>
                </div>
                <div class="form-check form-check-inline">
                    <input type="radio" name="shift_type" value="night" id="night" class="form-check-input" required>
                    <label for="night" class="form-check-label">Night (8PM - 10PM)</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Assign Shift</button>
            <a href="shift_monthly_index.php" class="btn btn-secondary">Cancel</a>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
