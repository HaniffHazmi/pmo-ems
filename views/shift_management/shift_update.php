<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /views/login.php");
    exit;
}

$date = $_GET['date'] ?? '';
$shift_type = $_GET['shift_type'] ?? '';

// Redirect if date or shift type is missing
if (!$date || !$shift_type) {
    header("Location: shift_monthly_index.php");
    exit;
}

// Get current shift assignment
$shifts = Shift::getAllByDate($date);
$currentStaffId = null;

foreach ($shifts as $shift) {
    if ($shift['shift_type'] === $shift_type) {
        $currentStaffId = $shift['staff_id'];
        break;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStaffId = $_POST['staff_id'] ?? '';
    
    if ($newStaffId) {
        // Delete existing shift if any
        if ($currentStaffId) {
            $stmt = $pdo->prepare("DELETE FROM shifts WHERE shift_date = ? AND shift_type = ?");
            $stmt->execute([$date, $shift_type]);
        }
        
        // Assign new shift
        Shift::assignShift($newStaffId, $shift_type, $date);
        
        // Redirect back to monthly view
        header("Location: shift_monthly_index.php?month=" . date('m', strtotime($date)) . "&year=" . date('Y', strtotime($date)));
        exit;
    }
}

// Get all staff members
$allStaff = Staff::getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Shift - <?= htmlspecialchars($date) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/shift-management.css" />
</head>
<body>
<?php include '../partials/navbar.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>Update Shift Assignment</h2>
        <p class="text-muted mb-4">
            <?= date('l, d M Y', strtotime($date)) ?> - 
            <?= $shift_type === 'evening' ? 'Evening Shift (4PM - 7PM)' : 'Night Shift (8PM - 10PM)' ?>
        </p>

        <form method="POST" class="update-form">
            <div class="mb-4">
                <label class="form-label">Select Staff Member:</label>
                <select name="staff_id" class="form-select" required>
                    <option value="">Select a staff member</option>
                    <?php foreach ($allStaff as $staff): ?>
                        <option value="<?= $staff['id'] ?>" <?= $currentStaffId == $staff['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($staff['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Assignment
                </button>
                <a href="shift_monthly_index.php?month=<?= date('m', strtotime($date)) ?>&year=<?= date('Y', strtotime($date)) ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Schedule
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 