<?php
session_start();
require_once '../../config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid staff ID.";
    header("Location: staff_index.php");
    exit;
}

$id = (int) $_GET['id'];

// Fetch staff data
$stmt = $pdo->prepare("SELECT * FROM staff WHERE id = ?");
$stmt->execute([$id]);
$staff = $stmt->fetch();

if (!$staff) {
    $_SESSION['error'] = "Staff not found.";
    header("Location: staff_index.php");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'];
    $email       = $_POST['email'];
    $matric_no   = $_POST['matric_no'];
    $phone_number = $_POST['phone_number'];

    $stmt = $pdo->prepare("UPDATE staff SET name = ?, email = ?, matric_no = ?, phone_number = ? WHERE id = ?");
    $stmt->execute([$name, $email, $matric_no, $phone_number, $id]);

    $_SESSION['success'] = "Staff updated successfully!";
    header("Location: staff_read.php?id=$id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Staff | PMO-EMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include '../partials/navbar.php'; ?>

  <div class="container mt-5">
    <h2>Edit Staff</h2>
    <form method="POST" class="mt-4">
      <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($staff['name']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($staff['email']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Matric No</label>
        <input type="text" name="matric_no" value="<?= htmlspecialchars($staff['matric_no']) ?>" class="form-control">
      </div>
      <div class="mb-3">
        <label>Phone Number</label>
        <input type="text" name="phone_number" value="<?= htmlspecialchars($staff['phone_number']) ?>" class="form-control">
      </div>
      <button type="submit" class="btn btn-success">Update</button>
      <a href="staff_read.php?id=<?= $staff['id'] ?>" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</body>
</html>
