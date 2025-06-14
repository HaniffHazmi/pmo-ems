<?php
session_start();
require_once '../../models/Shift.php';
require_once '../../models/Staff.php';

// Ensure only staff can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header("Location: /views/login.php");
    exit;
}

$staff = $_SESSION['user'];
$date = $_GET['date'] ?? '';
$shift_type = $_GET['shift_type'] ?? '';

// Redirect if date or shift type is missing
if (!$date || !$shift_type) {
    header("Location: staff_shift_monthly_index.php");
    exit;
}

// Check if the date is in the past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    header("Location: staff_shift_monthly_index.php");
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

// Ensure the current staff is assigned to this shift
if ($currentStaffId !== $staff['id']) {
    header("Location: staff_shift_monthly_index.php");
    exit;
}

// Delete the shift assignment
$stmt = $pdo->prepare("DELETE FROM shifts WHERE shift_date = ? AND shift_type = ? AND staff_id = ?");
$stmt->execute([$date, $shift_type, $staff['id']]);

// Redirect back to monthly view
header("Location: staff_shift_monthly_index.php?month=" . date('m', strtotime($date)) . "&year=" . date('Y', strtotime($date)));
exit; 