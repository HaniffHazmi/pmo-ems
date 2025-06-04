<?php
// views/staff_management/staff_delete.php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    $stmt = $pdo->prepare("DELETE FROM staff WHERE id = ?");
    if ($stmt->execute([$id])) {
        $_SESSION['success'] = "Staff deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete staff.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: staff_index.php");
exit;
