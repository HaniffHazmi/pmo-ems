<?php
// index.php - redirect to login page

require_once __DIR__ . '/models/Staff.php';
require_once __DIR__ . '/models/Shift.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

header("Location: views/login.php");
exit;
