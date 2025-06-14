<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pmo_ems';
$backupDir = __DIR__ . '/backups';
$date = date('Y-m-d_H-i-s');

// 1. Create backup folder if it doesn't exist
if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

// 2. SQL Backup
$sqlFile = "$backupDir/backup_{$date}.sql";
$command = "mysqldump --user=$username --password=$password --host=$host $database > $sqlFile";
system($command, $retval);

// 3. CSV Backup
$mysqli = new mysqli($host, $username, $password, $database);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$tables = ['admins', 'staff', 'shifts', 'salaries'];
$csvFiles = [];

foreach ($tables as $table) {
    $csvFile = "$backupDir/backup_{$date}_{$table}.csv";
    $csvFiles[] = $csvFile;

    $result = $mysqli->query("SELECT * FROM `$table`");
    if (!$result) continue;

    $fp = fopen($csvFile, 'w');

    // Write header
    $headers = array();
    while ($fieldinfo = $result->fetch_field()) {
        $headers[] = $fieldinfo->name;
    }
    fputcsv($fp, $headers);

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($fp, $row);
    }

    fclose($fp);
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Backup - PMO EMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">✅ PMO EMS Backup Completed</h4>
            </div>
            <div class="card-body">
                <p class="mb-3"><strong>Timestamp:</strong> <?= $date ?></p>

                <h5>📦 SQL Backup File</h5>
                <a href="backups/<?= basename($sqlFile) ?>" class="btn btn-outline-success mb-3" download>
                    Download SQL File (<?= basename($sqlFile) ?>)
                </a>

                <h5>📂 CSV Files Per Table</h5>
                <ul class="list-group">
                    <?php foreach ($csvFiles as $csv): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= basename($csv) ?>
                            <a href="backups/<?= basename($csv) ?>" class="btn btn-sm btn-outline-primary" download>Download</a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <a href="/views/admin/admin_dashboard.php" class="btn btn-secondary mt-4">⬅ Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
