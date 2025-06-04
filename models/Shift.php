<?php
// models/Shift.php

require_once __DIR__ . '/../config/database.php';

class Shift {
    public static function getAllByDate($date) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM shifts WHERE shift_date = ?");
        $stmt->execute([$date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getByStaffAndDate($staff_id, $date) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM shifts WHERE staff_id = ? AND shift_date = ?");
        $stmt->execute([$staff_id, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function assignShift($staff_id, $shift_type, $date) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO shifts (staff_id, shift_type, shift_date) VALUES (?, ?, ?)");
        return $stmt->execute([$staff_id, $shift_type, $date]);
    }

    public static function getForMonth($staff_id, $month) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM shifts WHERE staff_id = ? AND MONTH(shift_date) = ?");
        $stmt->execute([$staff_id, $month]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteByDate($date) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM shifts WHERE shift_date = ?");
        return $stmt->execute([$date]);
    }


    // In Staff.php
    

}
