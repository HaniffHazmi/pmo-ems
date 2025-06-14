<?php
// models/Staff.php

require_once __DIR__ . '/../config/database.php';

class Staff {

    public static function getById($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function findByEmail($email) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findById($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAll() {
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM staff");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function create($data) {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO staff (name, email, password, matric_no, phone_number) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $data['password'],
            $data['matric_no'],
            $data['phone_number']
        ]);
    }

    public static function delete($id) {
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getShiftStatistics($month = null) {
        global $pdo;
        
        if ($month === null) {
            $month = date('m');
        }
        
        // Get yesterday's date
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $query = "SELECT s.staff_id, st.name, COUNT(*) as total_shifts 
                 FROM shifts s 
                 JOIN staff st ON s.staff_id = st.id 
                 WHERE MONTH(s.shift_date) = ? 
                 AND s.shift_date <= ?
                 GROUP BY s.staff_id, st.name 
                 ORDER BY total_shifts DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$month, $yesterday]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
