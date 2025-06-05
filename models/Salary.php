<?php
// models/Salary.php

require_once __DIR__ . '/../config/database.php';

class Salary {
    public static function calculateMonthlySalary($staff_id, $month) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT shift_type, COUNT(*) as total FROM shifts WHERE staff_id = ? AND MONTH(shift_date) = ? GROUP BY shift_type");
        $stmt->execute([$staff_id, $month]);
        $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total = 0;
        foreach ($shifts as $shift) {
            if ($shift['shift_type'] === 'evening') {
                $total += 12 * $shift['total'];
            } elseif ($shift['shift_type'] === 'night') {
                $total += 8 * $shift['total'];
            }
        }

        return $total;
    }

    public static function recordPayment($staff_id, $month, $amount) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO salaries (staff_id, salary_month, amount_paid, paid_at) VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$staff_id, $month, $amount]);
    }

    public static function getAllForStaff($staff_id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT s.name AS staff_name, sal.salary_month, sal.amount_paid, sal.paid_at
        FROM salaries sal
        JOIN staff s ON sal.staff_id = s.id
        WHERE sal.staff_id = :staff_id
        ORDER BY sal.paid_at DESC");
        $stmt->execute(['staff_id' => $staff_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
