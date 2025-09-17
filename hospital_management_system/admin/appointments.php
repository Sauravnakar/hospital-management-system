<?php
// Admin appointments management page.
session_start();
if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}
require '../db.php';

$message = '';

// Fetch patients and doctors for dropdowns
$patients_list = [];
$res_patients = $conn->query("SELECT id, name FROM patients ORDER BY name ASC");
while($row = $res_patients->fetch_assoc()) {
    $patients_list[$row['id']] = $row['name'];
}
$doctors_list = [];
$res_doctors = $conn->query("SELECT id, name FROM doctors ORDER BY name ASC");
while($row = $res_doctors->fetch_assoc()) {
    $doctors_list[$row['id']] = $row['name'];
}

// Handle adding a new appointment
if (isset($_POST['add_appointment'])) {
    $patient_id      = intval($_POST['patient_id']);
    $doctor_id       = intval($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $status          = trim($_POST['status']);
    $description     = trim($_POST['description']);
    $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $patient_id, $doctor_id, $appointment_date, $appointment_time, $status, $description);
    if ($stmt->execute()) {
        $message = "Appointment scheduled successfully.";
    } else {
        $message = "Error scheduling appointment: " . $stmt->error;
    }
    $stmt->close();
}

// Handle updating an appointment
if (isset($_POST['update_appointment'])) {
    $id              = intval($_POST['id']);
    $patient_id      = intval($_POST['patient_id']);
    $doctor_id       = intval($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $status          = trim($_POST['status']);
    $description     = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE appointments SET patient_id=?, doctor_id=?, appointment_date=?, appointment_time=?, status=?, description=? WHERE id=?");
    $stmt->bind_param("iissssi", $patient_id, $doctor_id, $appointment_date, $appointment_time, $status, $description, $id);
    if ($stmt->execute()) {
        $message = "Appointment updated successfully.";
    } else {
        $message = "Error updating appointment: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: appointments.php');
    exit();
}

// Determine edit mode
$edit_mode = false;
$edit_appointment = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $edit_appointment = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all appointments with names
$appointments_res = $conn->query("SELECT a.*, p.name AS patient_name, d.name AS doctor_name FROM appointments a JOIN patients p ON a.patient_id = p.id JOIN doctors d ON a.doctor_id = d.id ORDER BY a.id DESC");
?>
<?php include 'header.php'; ?>
<h2 class="mb-4">Manage Appointments</h2>
<?php if($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_mode ? 'Edit Appointment' : 'Schedule New Appointment'; ?>
    </div>
    <div class="card-body">
        <form method="post" action="appointments.php">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_appointment" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_appointment['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_appointment" value="1">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Patient</label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">-- Select Patient --</option>
                        <?php foreach($patients_list as $pid => $pname): ?>
                            <option value="<?php echo $pid; ?>" <?php if($edit_mode && $edit_appointment['patient_id']==$pid) echo 'selected'; ?>><?php echo htmlspecialchars($pname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Doctor</label>
                    <select name="doctor_id" class="form-control" required>
                        <option value="">-- Select Doctor --</option>
                        <?php foreach($doctors_list as $did => $dname): ?>
                            <option value="<?php echo $did; ?>" <?php if($edit_mode && $edit_appointment['doctor_id']==$did) echo 'selected'; ?>><?php echo htmlspecialchars($dname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Status</label>
                    <?php $st = $edit_mode ? $edit_appointment['status'] : 'Scheduled'; ?>
                    <select name="status" class="form-control">
                        <option value="Scheduled" <?php echo ($st=='Scheduled')?'selected':''; ?>>Scheduled</option>
                        <option value="Completed" <?php echo ($st=='Completed')?'selected':''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo ($st=='Cancelled')?'selected':''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Date</label>
                    <input type="date" name="appointment_date" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_appointment['appointment_date']) : ''; ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Time</label>
                    <input type="time" name="appointment_time" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_appointment['appointment_time']) : ''; ?>" required>
                </div>
                <div class="form-group col-md-4">
                    <label>Description</label>
                    <input type="text" name="description" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_appointment['description']) : ''; ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Schedule'; ?> Appointment</button>
            <?php if($edit_mode): ?>
                <a href="appointments.php" class="btn btn-secondary ml-2">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $appointments_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                <td><?php echo htmlspecialchars($row['appointment_time']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td>
                    <a href="appointments.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="appointments.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>