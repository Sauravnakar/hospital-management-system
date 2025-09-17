<?php
// Admin doctors management page: CRUD operations for doctors.
session_start();
if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}
require '../db.php';

$message = '';

// Fetch departments for select options
$dept_res = $conn->query("SELECT id, name FROM departments ORDER BY name ASC");
$departments = [];
while($dept_row = $dept_res->fetch_assoc()) {
    $departments[$dept_row['id']] = $dept_row['name'];
}

// Handle adding a new doctor
if (isset($_POST['add_doctor'])) {
    $name         = trim($_POST['name']);
    $specialty    = trim($_POST['specialty']);
    $phone        = trim($_POST['phone']);
    $email        = trim($_POST['email']);
    $departmentId = intval($_POST['department_id']) ?: null;
    $qualification = trim($_POST['qualification']);
    $stmt = $conn->prepare("INSERT INTO doctors (name, specialty, phone, email, department_id, qualification) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $name, $specialty, $phone, $email, $departmentId, $qualification);
    if ($stmt->execute()) {
        $message = "Doctor added successfully.";
    } else {
        $message = "Error adding doctor: " . $stmt->error;
    }
    $stmt->close();
}

// Handle updating a doctor
if (isset($_POST['update_doctor'])) {
    $id           = intval($_POST['id']);
    $name         = trim($_POST['name']);
    $specialty    = trim($_POST['specialty']);
    $phone        = trim($_POST['phone']);
    $email        = trim($_POST['email']);
    $departmentId = intval($_POST['department_id']) ?: null;
    $qualification = trim($_POST['qualification']);
    $stmt = $conn->prepare("UPDATE doctors SET name = ?, specialty = ?, phone = ?, email = ?, department_id = ?, qualification = ? WHERE id = ?");
    $stmt->bind_param("ssssisi", $name, $specialty, $phone, $email, $departmentId, $qualification, $id);
    if ($stmt->execute()) {
        $message = "Doctor updated successfully.";
    } else {
        $message = "Error updating doctor: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deletion of doctor
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: doctors.php');
    exit();
}

// Determine edit mode
$edit_mode = false;
$edit_doctor = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $edit_doctor = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all doctors with department names
$doctors_res = $conn->query("SELECT d.*, dep.name AS department_name FROM doctors d LEFT JOIN departments dep ON d.department_id = dep.id ORDER BY d.id DESC");
?>
<?php include 'header.php'; ?>
<h2 class="mb-4">Manage Doctors</h2>
<?php if($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_mode ? 'Edit Doctor' : 'Add New Doctor'; ?>
    </div>
    <div class="card-body">
        <form method="post" action="doctors.php">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_doctor" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_doctor['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_doctor" value="1">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_doctor['name']) : ''; ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Specialty</label>
                    <input type="text" name="specialty" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_doctor['specialty']) : ''; ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_doctor['phone']) : ''; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_doctor['email']) : ''; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label>Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">-- Select --</option>
                        <?php foreach($departments as $deptId => $deptName): ?>
                            <option value="<?php echo $deptId; ?>" <?php if($edit_mode && $edit_doctor['department_id'] == $deptId) echo 'selected'; ?>><?php echo htmlspecialchars($deptName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Qualification</label>
                <input type="text" name="qualification" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_doctor['qualification']) : ''; ?>">
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Doctor</button>
            <?php if($edit_mode): ?>
                <a href="doctors.php" class="btn btn-secondary ml-2">Cancel</a>
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
                <th>Specialty</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Department</th>
                <th>Qualification</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $doctors_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                <td><?php echo htmlspecialchars($row['qualification']); ?></td>
                <td>
                    <a href="doctors.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="doctors.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this doctor?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>