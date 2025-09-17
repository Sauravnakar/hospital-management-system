<?php
// Admin departments management page: CRUD operations for departments.
session_start();
if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}
require '../db.php';

$message = '';

// Handle adding a new department
if (isset($_POST['add_department'])) {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    if ($stmt->execute()) {
        $message = "Department added successfully.";
    } else {
        $message = "Error adding department: " . $stmt->error;
    }
    $stmt->close();
}

// Handle updating a department
if (isset($_POST['update_department'])) {
    $id          = intval($_POST['id']);
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE departments SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $description, $id);
    if ($stmt->execute()) {
        $message = "Department updated successfully.";
    } else {
        $message = "Error updating department: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: departments.php');
    exit();
}

// Determine edit mode
$edit_mode = false;
$edit_department = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM departments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $edit_department = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all departments
$departments_res = $conn->query("SELECT * FROM departments ORDER BY id DESC");
?>
<?php include 'header.php'; ?>
<h2 class="mb-4">Manage Departments</h2>
<?php if($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_mode ? 'Edit Department' : 'Add New Department'; ?>
    </div>
    <div class="card-body">
        <form method="post" action="departments.php">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_department" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_department['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_department" value="1">
            <?php endif; ?>
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_department['name']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"><?php echo $edit_mode ? htmlspecialchars($edit_department['description']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Department</button>
            <?php if($edit_mode): ?>
                <a href="departments.php" class="btn btn-secondary ml-2">Cancel</a>
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
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $departments_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td>
                    <a href="departments.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="departments.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>