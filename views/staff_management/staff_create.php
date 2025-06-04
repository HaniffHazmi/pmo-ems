<?php
session_start();
require_once '../../config/database.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name']);
    $email        = trim($_POST['email']);
    $password     = trim($_POST['password']);
    $matric_no    = trim($_POST['matric_no']);
    $phone_number = trim($_POST['phone_number']);

    // Basic validation
    if (empty($name))         $errors[] = "Name is required.";
    if (empty($email))        $errors[] = "Email is required.";
    if (empty($password))     $errors[] = "Password is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO staff (name, email, password, matric_no, phone_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $password, $matric_no, $phone_number]);

            $_SESSION['success'] = "Staff created successfully!";
            header("Location: staff_index.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error inserting data: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Staff | PMO-EMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include '../partials/navbar.php'; ?>

  <div class="container mt-5">
    <h2>Create New Staff</h2>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
      <div class="mb-3">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label>Password</label>
        <input type="text" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Matric No</label>
        <input type="text" name="matric_no" class="form-control" value="<?= htmlspecialchars($_POST['matric_no'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label>Phone Number</label>
        <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>">
      </div>
      <button type="submit" class="btn btn-success">Create Staff</button>
      <a href="staff_index.php" class="btn btn-secondary">Cancel</a>
    </form>
  </div>
</body>
</html>
