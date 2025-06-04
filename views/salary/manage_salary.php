<?php
// manage_salary.php - Salary Management System with Shift Calculation
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Salary.php';
require_once __DIR__ . '/../../models/Staff.php';

include '../../views/partials/navbar.php';

// Initialize variables
$message = '';
$salary = [
    'staff_id' => '',
    'salary_month' => date('n'),
    'amount_paid' => '',
    'calculated_amount' => 0
];

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 
    4 => 'April', 5 => 'May', 6 => 'June', 
    7 => 'July', 8 => 'August', 9 => 'September', 
    10 => 'October', 11 => 'November', 12 => 'December'
];

// Fetch staff for dropdown
try {
    $staff = Staff::getAll();
} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Error fetching staff: ' . $e->getMessage() . '</div>';
}

// Process calculate salary request
if (isset($_GET['calculate'])) {
    $staff_id = filter_input(INPUT_GET, 'staff_id', FILTER_VALIDATE_INT);
    $month = filter_input(INPUT_GET, 'month', FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 1, 'max_range' => 12]]);
    
    if ($staff_id && $month) {
        try {
            $calculated_amount = Salary::calculateMonthlySalary($staff_id, $month);
            $salary['calculated_amount'] = $calculated_amount;
            $salary['staff_id'] = $staff_id;
            $salary['salary_month'] = $month;
            $message = '<div class="alert alert-info">Calculated salary: $' . number_format($calculated_amount, 2) . '</div>';
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Calculation error: ' . $e->getMessage() . '</div>';
        }
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
    $salary_month = filter_input(INPUT_POST, 'salary_month', FILTER_VALIDATE_INT, 
        ['options' => ['min_range' => 1, 'max_range' => 12]]);
    $amount_paid = filter_input(INPUT_POST, 'amount_paid', FILTER_VALIDATE_FLOAT);
    
    if ($staff_id && $salary_month && $amount_paid) {
        try {
            $success = Salary::recordPayment($staff_id, $salary_month, $amount_paid);
            
            if ($success) {
                $message = '<div class="alert alert-success">Salary payment recorded successfully!</div>';
                $salary = [
                    'staff_id' => '',
                    'salary_month' => date('n'),
                    'amount_paid' => '',
                    'calculated_amount' => 0
                ];
            } else {
                $message = '<div class="alert alert-danger">Failed to record payment.</div>';
            }
        } catch (Exception $e) {
            $message = '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Please fill all fields with valid data!</div>';
        $salary = [
            'staff_id' => $_POST['staff_id'] ?? '',
            'salary_month' => $_POST['salary_month'] ?? date('n'),
            'amount_paid' => $_POST['amount_paid'] ?? '',
            'calculated_amount' => $_POST['calculated_amount'] ?? 0
        ];
    }
}

// Fetch all salary records
try {
    $salaries = Salary::getAllForStaff($salary['staff_id']);
} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Error fetching salary records: ' . $e->getMessage() . '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .month-name { text-transform: capitalize; }
        .calculated-amount { font-weight: bold; color: #28a745; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Salary Management</h2>
        <?php echo $message; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Process Salary Payment</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="staff_id" class="form-label">Staff Member</label>
                                <select class="form-select" id="staff_id" name="staff_id" required>
                                    <option value="">Select Staff</option>
                                    <?php foreach ($staff as $member): ?>
                                        <option value="<?= htmlspecialchars($member['id']) ?>" 
                                            <?= ($salary['staff_id'] == $member['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($member['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="salary_month" class="form-label">Month</label>
                                <select class="form-select" id="salary_month" name="salary_month" required>
                                    <?php foreach ($months as $num => $name): ?>
                                        <option value="<?= $num ?>" 
                                            <?= ($salary['salary_month'] == $num) ? 'selected' : '' ?>>
                                            <?= $name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <a href="#" class="btn btn-outline-secondary" id="calculate-btn">
                                    Calculate Salary
                                </a>
                                <?php if ($salary['calculated_amount'] > 0): ?>
                                    <span class="calculated-amount ms-3">
                                        Calculated: RM<?= number_format($salary['calculated_amount'], 2) ?>
                                    </span>
                                    <input type="hidden" name="calculated_amount" value="<?= $salary['calculated_amount'] ?>">
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="amount_paid" class="form-label">Amount Paid</label>
                                <div class="input-group">
                                    <span class="input-group-text">RM</span>
                                    <input type="number" class="form-control" id="amount_paid" name="amount_paid" 
                                           step="0.01" min="0" required 
                                           value="<?= $salary['calculated_amount'] > 0 ? $salary['calculated_amount'] : htmlspecialchars($salary['amount_paid']) ?>">
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Record Payment</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Salary Payment History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($salaries)): ?>
                            <p>No salary records found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Staff</th>
                                            <th>Month</th>
                                            <th>Amount</th>
                                            <th>Paid On</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($salaries as $record): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($record['staff_name'] ?? 'Unknown') ?></td>
                                                <td class="month-name"><?= $months[$record['salary_month']] ?? $record['salary_month'] ?></td>
                                                <td>$<?= number_format($record['amount_paid'], 2) ?></td>
                                                <td><?= date('M j, Y H:i', strtotime($record['paid_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('calculate-btn').addEventListener('click', function(e) {
            e.preventDefault();
            const staffId = document.getElementById('staff_id').value;
            const month = document.getElementById('salary_month').value;
            
            if (!staffId) {
                alert('Please select a staff member first');
                return;
            }
            
            window.location.href = `?calculate=1&staff_id=${encodeURIComponent(staffId)}&month=${encodeURIComponent(month)}`;
        });
    </script>
</body>
</html>