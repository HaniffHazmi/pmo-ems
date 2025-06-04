<?php
require_once '../../models/Shift.php';

$date = $_GET['date'] ?? null;

if ($date) {
    Shift::deleteByDate($date);
    header("Location: /views/shift_management/shift_monthly_index.php");
    exit;
} else {
    echo "Invalid date.";
}
