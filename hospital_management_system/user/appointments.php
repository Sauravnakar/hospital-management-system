<?php
// Displays a list of appointments to regular users.  Appointments are read-only
// and show patient and doctor details.  Users cannot edit or delete appointments.
session_start();
if (!isset($_SESSION['user_id']) || !empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}

require '../db.php';

// Fetch all appointments along with patient and doctor names
$query = "SELECT a.*, p.name AS patient_name, d.name AS doctor_name
          FROM appointments a
          JOIN patients p ON a.patient_id = p.id
          JOIN doctors d ON a.doctor_id = d.id
          ORDER BY a.appointment_date DESC, a.appointment_time DESC";
$result = $conn->query($query);

include 'header.php';
?>
<h2 class="mb-4">Appointments</h2>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['appointment_time'], 0, 5)); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No appointments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>