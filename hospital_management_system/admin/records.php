<?php
// Admin medical records management page.
session_start();
if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}
require '../db.php';

$message = '';

// Fetch patients and doctors for dropdowns
$patients = [];
$res_patients = $conn->query("SELECT id, name FROM patients ORDER BY name ASC");
while($row = $res_patients->fetch_assoc()) {
    $patients[$row['id']] = $row['name'];
}
$doctors = [];
$res_doctors = $conn->query("SELECT id, name FROM doctors ORDER BY name ASC");
while($row = $res_doctors->fetch_assoc()) {
    $doctors[$row['id']] = $row['name'];
}

// Handle adding a new record
if (isset($_POST['add_record'])) {
    $patient_id  = intval($_POST['patient_id']);
    $doctor_id   = intval($_POST['doctor_id']);
    $diagnosis   = trim($_POST['diagnosis']);
    $treatment   = trim($_POST['treatment']);
    $record_date = $_POST['record_date'];
    $notes       = trim($_POST['notes']);
    $stmt = $conn->prepare("INSERT INTO records (patient_id, doctor_id, diagnosis, treatment, record_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $patient_id, $doctor_id, $diagnosis, $treatment, $record_date, $notes);
    if ($stmt->execute()) {
        $message = "Medical record added successfully.";
    } else {
        $message = "Error adding record: " . $stmt->error;
    }
    $stmt->close();
}

// Handle updating a record
if (isset($_POST['update_record'])) {
    $id         = intval($_POST['id']);
    $patient_id = intval($_POST['patient_id']);
    $doctor_id  = intval($_POST['doctor_id']);
    $diagnosis  = trim($_POST['diagnosis']);
    $treatment  = trim($_POST['treatment']);
    $record_date= $_POST['record_date'];
    $notes      = trim($_POST['notes']);
    $stmt = $conn->prepare("UPDATE records SET patient_id=?, doctor_id=?, diagnosis=?, treatment=?, record_date=?, notes=? WHERE id=?");
    $stmt->bind_param("iissssi", $patient_id, $doctor_id, $diagnosis, $treatment, $record_date, $notes, $id);
    if ($stmt->execute()) {
        $message = "Medical record updated successfully.";
    } else {
        $message = "Error updating record: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: records.php');
    exit();
}

// Determine edit mode
$edit_mode = false;
$edit_record = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM records WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $edit_record = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all records with names
$records_res = $conn->query("SELECT r.*, p.name AS patient_name, d.name AS doctor_name FROM records r JOIN patients p ON r.patient_id = p.id JOIN doctors d ON r.doctor_id = d.id ORDER BY r.id DESC");
?>
<?php include 'header.php'; ?>
<h2 class="mb-4">Manage Medical Records</h2>
<?php if($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_mode ? 'Edit Medical Record' : 'Add New Medical Record'; ?>
    </div>
    <div class="card-body">
        <form method="post" action="records.php">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_record" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_record['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_record" value="1">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Patient</label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">-- Select Patient --</option>
                        <?php foreach($patients as $pid => $pname): ?>
                            <option value="<?php echo $pid; ?>" <?php if($edit_mode && $edit_record['patient_id']==$pid) echo 'selected'; ?>><?php echo htmlspecialchars($pname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Doctor</label>
                    <select name="doctor_id" class="form-control" required>
                        <option value="">-- Select Doctor --</option>
                        <?php foreach($doctors as $did => $dname): ?>
                            <option value="<?php echo $did; ?>" <?php if($edit_mode && $edit_record['doctor_id']==$did) echo 'selected'; ?>><?php echo htmlspecialchars($dname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Date</label>
                    <input type="date" name="record_date" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_record['record_date']) : ''; ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Diagnosis</label>
                <textarea name="diagnosis" class="form-control" rows="2" required><?php echo $edit_mode ? htmlspecialchars($edit_record['diagnosis']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Treatment</label>
                <textarea name="treatment" class="form-control" rows="2" required><?php echo $edit_mode ? htmlspecialchars($edit_record['treatment']) : ''; ?></textarea>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="2"><?php echo $edit_mode ? htmlspecialchars($edit_record['notes']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Record</button>
            <?php if($edit_mode): ?>
                <a href="records.php" class="btn btn-secondary ml-2">Cancel</a>
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
                <th>Diagnosis</th>
                <th>Treatment</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $records_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                <td><?php echo htmlspecialchars($row['record_date']); ?></td>
                <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                <td><?php echo htmlspecialchars($row['treatment']); ?></td>
                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                <td>
                    <a href="records.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="records.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>