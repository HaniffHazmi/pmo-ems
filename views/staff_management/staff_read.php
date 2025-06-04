<?php
session_start();
require_once '../../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid staff ID.";
    header("Location: staff_index.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->execute([$id]);
$staff = $stmt->fetch();

if (!$staff) {
    $_SESSION['error'] = "Staff not found.";
    header("Location: staff_index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Details | PMO-EMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include '../partials/navbar.php'; ?>

  <div class="container mt-5">
    <h2>Staff Details</h2>
    <table class="table table-bordered mt-4">
      <tr>
        <th>ID</th>
        <td><?= $staff['id'] ?></td>
      </tr>
      <tr>
        <th>Name</th>
        <td><?= htmlspecialchars($staff['name']) ?></td>
      </tr>
      <tr>
        <th>Email</th>
        <td><?= htmlspecialchars($staff['email']) ?></td>
      </tr>
      <tr>
        <th>Matric No</th>
        <td><?= htmlspecialchars($staff['matric_no']) ?></td>
      </tr>
      <tr>
        <th>Phone Number</th>
        <td><?= htmlspecialchars($staff['phone_number']) ?></td>
      </tr>
      <tr>
        <th>Created At</th>
        <td><?= $staff['created_at'] ?></td>
      </tr>
    </table>
    <a href="staff_index.php" class="btn btn-secondary">Back to List</a>
    <a href="staff_edit.php?id=<?= $staff['id'] ?>" class="btn btn-primary">Edit</a>
  </div>
</body>
</html>
