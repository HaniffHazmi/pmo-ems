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

    public static function getUnpaidSalaries($month, $year) {
        global $pdo;
        
        $query = "SELECT s.id as staff_id, s.name as staff_name, 
                        COALESCE(sa.amount_paid, 0) as calculated_amount
                 FROM staff s
                 LEFT JOIN (
                     SELECT staff_id, amount_paid
                     FROM salaries
                     WHERE salary_month = ? AND YEAR(paid_at) = ?
                 ) sa ON s.id = sa.staff_id
                 WHERE sa.staff_id IS NULL
                 ORDER BY s.name";
                 
        $stmt = $pdo->prepare($query);
        $stmt->execute([$month, $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSalarySummary($month, $year, $staffId = null) {
        global $pdo;
        
        $query = "SELECT 
                    s.id as staff_id, 
                    s.name as staff_name,
                    s.salary as base_salary,
                    COALESCE(shift_totals.total_amount, 0) as shift_amount,
                    (s.salary + COALESCE(shift_totals.total_amount, 0)) as total_salary,
                    COALESCE(sp.amount_paid, 0) as amount_paid
                 FROM staff s
                 LEFT JOIN (
                     SELECT 
                        staff_id,
                        SUM(CASE 
                            WHEN shift_type = 'evening' THEN 12
                            WHEN shift_type = 'night' THEN 8
                            ELSE 0
                        END) as total_amount
                     FROM shifts
                     WHERE MONTH(shift_date) = ? AND YEAR(shift_date) = ?
                     GROUP BY staff_id
                 ) shift_totals ON s.id = shift_totals.staff_id
                 LEFT JOIN (
                     SELECT staff_id, SUM(amount_paid) as amount_paid
                     FROM salaries
                     WHERE salary_month = ? AND YEAR(paid_at) = ?
                     GROUP BY staff_id
                 ) sp ON s.id = sp.staff_id
                 WHERE 1=1";
        
        $params = [$month, $year, $month, $year];
        
        if ($staffId && $staffId !== 'all') {
            $query .= " AND s.id = ?";
            $params[] = $staffId;
        }
        
        $query .= " ORDER BY s.name";
                 
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
