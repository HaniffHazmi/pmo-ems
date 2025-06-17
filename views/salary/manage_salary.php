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
$selectedStaffId = $_POST['staff_id'] ?? 'all';

// Get salary summary for all staff
$salarySummary = [];
if ($selectedStaffId === 'all') {
    $salarySummary = Salary::getSalarySummary($selectedMonth, $selectedYear);
} else {
    $salarySummary = Salary::getSalarySummary($selectedMonth, $selectedYear, $selectedStaffId);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_payment'])) {
    $staffId = $_POST['staff_id'];
    if ($staffId === 'all') {
        $_SESSION['error'] = "Please select a specific staff member to record payment.";
    } else {
        $month = $_POST['salary_month'];
        $amount = $_POST['amount_paid'];

        if (Salary::recordPayment($staffId, $month, $amount)) {
            $_SESSION['success'] = "Payment recorded successfully!";
        } else {
            $_SESSION['error'] = "Failed to record payment.";
        }
    }
    header("Location: manage_salary.php");
    exit;
}

// Get payment history for selected staff
$paymentHistory = [];
if ($selectedStaffId && $selectedStaffId !== 'all') {
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
                    <select class="form-select" id="staff_id" name="staff_id">
                        <option value="all" <?= ($selectedStaffId === 'all') ? 'selected' : '' ?>>All Staff</option>
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
                        <i class="fas fa-sync"></i> Refresh
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($salarySummary)): ?>
            <div class="table-responsive mt-4">
                <h3 class="mb-4">Salary Summary for <?= date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) ?></h3>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Staff Name</th>
                            <th class="text-end">Total Salary</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Unpaid</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalSalary = 0;
                        $totalPaid = 0;
                        $totalUnpaid = 0;
                        foreach ($salarySummary as $salary): 
                            $unpaid = $salary['total_salary'] - $salary['amount_paid'];
                            $totalSalary += $salary['total_salary'];
                            $totalPaid += $salary['amount_paid'];
                            $totalUnpaid += $unpaid;
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($salary['staff_name']) ?></td>
                                <td class="text-end">RM <?= number_format($salary['total_salary'], 2) ?></td>
                                <td class="text-end">RM <?= number_format($salary['amount_paid'], 2) ?></td>
                                <td class="text-end">RM <?= number_format($unpaid, 2) ?></td>
                                <td>
                                    <?php if ($salary['amount_paid'] >= $salary['total_salary']): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($salary['amount_paid'] > 0): ?>
                                        <span class="badge bg-warning">Partially Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($salary['amount_paid'] < $salary['total_salary']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="staff_id" value="<?= $salary['staff_id'] ?>">
                                            <input type="hidden" name="salary_month" value="<?= $selectedMonth ?>">
                                            <input type="hidden" name="amount_paid" value="<?= $salary['total_salary'] ?>">
                                            <button type="submit" name="record_payment" class="btn btn-success btn-sm">
                                                <i class="fas fa-check"></i> Pay
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-info">
                            <td><strong>Total</strong></td>
                            <td class="text-end"><strong>RM <?= number_format($totalSalary, 2) ?></strong></td>
                            <td class="text-end"><strong>RM <?= number_format($totalPaid, 2) ?></strong></td>
                            <td class="text-end"><strong>RM <?= number_format($totalUnpaid, 2) ?></strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
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