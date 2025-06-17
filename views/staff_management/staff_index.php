<?php
session_start();
require_once '../../models/Staff.php';

include '../../views/partials/navbar.php';

$staff = Staff::getAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Staff Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="/assets/css/staff-management.css" />
</head>
<body>
<div class="staff-container">
    <div class="staff-header">
        <h1 class="staff-title">Staff Management</h1>
        <p class="staff-subtitle">Manage your staff members and their details</p>
    </div>

    <div class="search-filter-container">
        <div class="form-group">
            <label for="search" class="form-label">Search Staff</label>
            <input type="text" id="search" class="form-control" placeholder="Search by name or ID...">
        </div>
        <a href="staff_create.php" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Add New Staff
        </a>
    </div>

    <div class="table-responsive">
        <table class="table" id="staffTable">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($staff as $member): ?>
                <tr>
                    <td data-label="ID"><?= htmlspecialchars($member['id']) ?></td>
                    <td data-label="Name"><?= htmlspecialchars($member['name']) ?></td>
                    <td data-label="Email"><?= htmlspecialchars($member['email']) ?></td>
                    <td data-label="Phone"><?= htmlspecialchars($member['phone_number']) ?></td>
                    <td data-label="Actions">
                        <div class="action-buttons">
                            <a href="staff_read.php?id=<?= $member['id'] ?>" class="btn btn-primary" title="View Details">
                                <i class="fas fa-eye"></i>
                                <span>View</span>
                            </a>
                            <a href="staff_edit.php?id=<?= $member['id'] ?>" class="btn btn-success" title="Edit Staff">
                                <i class="fas fa-edit"></i>
                                <span>Edit</span>
                            </a>
                            <form method="POST" action="staff_delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this staff member? This will also delete all their shifts and salary records.');">
                                <input type="hidden" name="id" value="<?= $member['id'] ?>">
                                <button type="submit" class="btn btn-danger" title="Delete Staff">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const table = document.getElementById('staffTable');
    const rows = table.querySelectorAll('tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();

        rows.forEach(row => {
            const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            const id = row.querySelector('td:nth-child(1)').textContent.toLowerCase();

            const matchesSearch = name.includes(searchTerm) || id.includes(searchTerm);
            row.style.display = matchesSearch ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
});
</script>
</body>
</html>
