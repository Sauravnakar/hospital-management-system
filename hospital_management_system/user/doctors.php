<?php
// Displays a list of doctors to regular users.  Users cannot edit doctors
// but can view important details like specialty and contact information.
session_start();
// Redirect if not logged in or if logged in as an admin
if (!isset($_SESSION['user_id']) || !empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}

require '../db.php';

// Fetch doctors with their department names
$query  = "SELECT d.*, dept.name AS department_name
           FROM doctors d
           LEFT JOIN departments dept ON d.department_id = dept.id";
$result = $conn->query($query);

// Include header
include 'header.php';
?>
<h2 class="mb-4">Doctors</h2>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Name</th>
                <th>Specialty</th>
                <th>Department</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Qualification</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                        <td><?php echo htmlspecialchars($row['department_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['qualification']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No doctors found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>