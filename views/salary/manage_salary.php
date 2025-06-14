<?php
// manage_salary.php - Salary Management System with Shift Calculation
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Salary.php';
require_once __DIR__ . '/../../models/Staff.php';

include '../../views/partials/navbar.php';

// Set timezone to Malaysia (GMT+8)
date_default_timezone_set('Asia/Kuala_Lumpur');

// Get all staff members
$staff = Staff::getAll();

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Get selected month and year from form or use current
$selectedMonth = $_POST['salary_month'] ?? $currentMonth;
$selectedYear = $_POST['salary_year'] ?? $currentYear;

// Get selected staff ID
$selectedStaffId = $_POST['staff_id'] ?? null;

// Calculate salary if staff and month are selected
$calculatedSalary = null;
if ($selectedStaffId && $selectedMonth) {
    $calculatedSalary = Salary::calculateMonthlySalary($selectedStaffId, $selectedMonth);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_payment'])) {
    $staffId = $_POST['staff_id'];
    $month = $_POST['salary_month'];
    $amount = $_POST['amount_paid'];

    if (Salary::recordPayment($staffId, $month, $amount)) {
        $_SESSION['success'] = "Payment recorded successfully!";
    } else {
        $_SESSION['error'] = "Failed to record payment.";
    }
    header("Location: manage_salary.php");
    exit;
}

// Get payment history for selected staff
$paymentHistory = [];
if ($selectedStaffId) {
    $paymentHistory = Salary::getAllForStaff($selectedStaffId);
}

// Generate months array
$months = [];
for ($i = 1; $i <= 12; $i++) {
    $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Management | PMO-EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/salary-management.css">
</head>
<body>
    <div class="salary-container">
        <div class="salary-header">
            <h1 class="salary-title">Salary Management</h1>
            <p class="salary-subtitle">Calculate and manage staff salaries</p>
        </div>

        <div class="form-container">
            <form method="POST" class="row g-3">
                <div class="col-md-4">
                    <label for="staff_id" class="form-label">Staff Member</label>
                    <select class="form-select" id="staff_id" name="staff_id" required>
                        <option value="">Select Staff</option>
                        <?php foreach ($staff as $member): ?>
                            <option value="<?= $member['id'] ?>" 
                                <?= ($selectedStaffId == $member['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($member['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="salary_month" class="form-label">Month</label>
                    <select class="form-select" id="salary_month" name="salary_month" required>
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?= $num ?>" 
                                <?= ($selectedMonth == $num) ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="salary_year" class="form-label">Year</label>
                    <select class="form-select" id="salary_year" name="salary_year" required>
                        <?php for ($year = $currentYear; $year >= $currentYear - 2; $year--): ?>
                            <option value="<?= $year ?>" 
                                <?= ($selectedYear == $year) ? 'selected' : '' ?>>
                                <?= $year ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calculator"></i> Calculate Salary
                    </button>
                </div>
            </form>
        </div>

        <?php if ($calculatedSalary !== null): ?>
            <div class="form-container">
                <h3 class="mb-4">Salary Details</h3>
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="mb-2">Calculated Amount:</p>
                        <div class="amount-display">
                            RM <?= number_format($calculatedSalary, 2) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="staff_id" value="<?= $selectedStaffId ?>">
                            <input type="hidden" name="salary_month" value="<?= $selectedMonth ?>">
                            <input type="hidden" name="amount_paid" value="<?= $calculatedSalary ?>">
                            <div class="col-12">
                                <button type="submit" name="record_payment" class="btn btn-success">
                                    <i class="fas fa-check"></i> Record Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($paymentHistory)): ?>
            <div class="table-responsive">
                <h3 class="mb-4">Payment History</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Amount Paid</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paymentHistory as $payment): ?>
                            <tr>
                                <td data-label="Month"><?= date('F Y', mktime(0, 0, 0, $payment['salary_month'], 1)) ?></td>
                                <td data-label="Amount">RM <?= number_format($payment['amount_paid'], 2) ?></td>
                                <td data-label="Payment Date"><?= date('d M Y H:i', strtotime($payment['paid_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>