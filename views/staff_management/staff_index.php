<?php
session_start();
require_once '../../config/database.php';

// Pagination setup
$limit = 10; // Number of staff per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Fetch staff list
$stmt = $pdo->prepare("SELECT * FROM staff ORDER BY created_at DESC LIMIT :start, :limit");
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->execute();
$staffList = $stmt->fetchAll();

// Total staff count for pagination
$totalStmt = $pdo->query("SELECT COUNT(*) FROM staff");
$totalStaff = $totalStmt->fetchColumn();
$totalPages = ceil($totalStaff / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Management | PMO-EMS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <?php include '../partials/navbar.php'; ?>

  <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Staff Management</h2>
      <a href="staff_create.php" class="btn btn-success">+ Add New Staff</a>
    </div>

    <?php if (count($staffList) === 0): ?>
      <div class="alert alert-warning">No staff records found.</div>
    <?php else: ?>
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Matric No</th>
            <th>Phone</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($staffList as $staff): ?>
            <tr>
              <td><?= htmlspecialchars($staff['id']) ?></td>
              <td><?= htmlspecialchars($staff['name']) ?></td>
              <td><?= htmlspecialchars($staff['email']) ?></td>
              <td><?= htmlspecialchars($staff['matric_no']) ?></td>
              <td><?= htmlspecialchars($staff['phone_number']) ?></td>
              <td><?= htmlspecialchars($staff['created_at']) ?></td>
              <td>
                <a href="staff_read.php?id=<?= $staff['id'] ?>" class="btn btn-sm btn-info">View</a>
                <a href="staff_edit.php?id=<?= $staff['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                <form action="staff_delete.php" method="POST" onsubmit="return confirm('Are you sure to delete this staff?');" style="display:inline;">
                    <input type="hidden" name="id" value="<?= $staff['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>

              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Pagination links -->
      <nav>
        <ul class="pagination">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  </div>
</body>
</html>
