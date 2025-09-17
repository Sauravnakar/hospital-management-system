<?php
// Admin patients management page: list, create, update and delete patient records.
session_start();
// Only accessible by admin users
if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}
require '../db.php';

$message = '';

// Handle adding a new patient
if (isset($_POST['add_patient'])) {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $gender  = trim($_POST['gender']);
    $dob     = $_POST['dob'] ?: null;
    $address = trim($_POST['address']);
    $stmt = $conn->prepare("INSERT INTO patients (name, email, phone, gender, dob, address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $phone, $gender, $dob, $address);
    if ($stmt->execute()) {
        $message = "Patient added successfully.";
    } else {
        $message = "Error adding patient: " . $stmt->error;
    }
    $stmt->close();
}

// Handle updating a patient
if (isset($_POST['update_patient'])) {
    $id      = intval($_POST['id']);
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $gender  = trim($_POST['gender']);
    $dob     = $_POST['dob'] ?: null;
    $address = trim($_POST['address']);
    $stmt = $conn->prepare("UPDATE patients SET name = ?, email = ?, phone = ?, gender = ?, dob = ?, address = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $name, $email, $phone, $gender, $dob, $address, $id);
    if ($stmt->execute()) {
        $message = "Patient updated successfully.";
    } else {
        $message = "Error updating patient: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: patients.php');
    exit();
}

// Determine if editing a patient
$edit_mode  = false;
$edit_patient = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $res->bind_param("i", $id);
    $res->execute();
    $result = $res->get_result();
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $edit_patient = $result->fetch_assoc();
    }
    $res->close();
}

// Fetch all patients for listing
$patients_res = $conn->query("SELECT * FROM patients ORDER BY id DESC");
?>
<?php include 'header.php'; ?>
<h2 class="mb-4">Manage Patients</h2>
<?php if($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_mode ? 'Edit Patient' : 'Add New Patient'; ?>
    </div>
    <div class="card-body">
        <form method="post" action="patients.php">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_patient" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_patient['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_patient" value="1">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_patient['name']) : ''; ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_patient['email']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_patient['phone']) : ''; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <?php $g = $edit_mode ? $edit_patient['gender'] : ''; ?>
                        <option value="Male" <?php echo ($g == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($g == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($g == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_patient['dob']) : ''; ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" class="form-control" rows="2"><?php echo $edit_mode ? htmlspecialchars($edit_patient['address']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Patient</button>
            <?php if($edit_mode): ?>
                <a href="patients.php" class="btn btn-secondary ml-2">Cancel</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $patients_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                <td><?php echo htmlspecialchars($row['dob']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td>
                    <a href="patients.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="patients.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this patient?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>