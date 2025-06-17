<?php
// views/staff_management/staff_delete.php
session_start();
require_once '../../models/Staff.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    
    try {
        if (Staff::delete($id)) {
            $_SESSION['success'] = "Staff member and all associated records have been deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete staff member.";
        }
    } catch (PDOException $e) {
        // Check if it's a foreign key constraint error
        if ($e->getCode() == 23000) {
            $_SESSION['error'] = "Cannot delete staff member because they have associated records. Please delete their shifts and salary records first.";
        } else {
            $_SESSION['error'] = "An error occurred while deleting the staff member.";
        }
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: staff_index.php");
exit;
