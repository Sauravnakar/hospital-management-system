<?php
// Displays a list of departments for regular users.  View-only.
session_start();
// Redirect if not logged in or if logged in as admin
if (!isset($_SESSION['user_id']) || !empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}

require '../db.php';

// Fetch departments
$result = $conn->query("SELECT * FROM departments");

include 'header.php';
?>
<h2 class="mb-4">Departments</h2>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Name</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="2" class="text-center">No departments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>