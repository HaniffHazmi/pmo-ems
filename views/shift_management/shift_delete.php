<?php
session_start();
require_once '../../models/Shift.php';

$date = $_GET['date'] ?? null;

if ($date) {
    Shift::deleteByDate($date);
}

header('Location: shift_monthly_index.php');
exit;
