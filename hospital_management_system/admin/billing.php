<?php
// Admin billing management page: manage bills/invoices.
session_start();
if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}
require '../db.php';

$message = '';

// Fetch patients for select options
$patients = [];
$res_patients = $conn->query("SELECT id, name FROM patients ORDER BY name ASC");
while($row = $res_patients->fetch_assoc()) {
    $patients[$row['id']] = $row['name'];
}

// Handle adding a new bill
if (isset($_POST['add_bill'])) {
    $patient_id = intval($_POST['patient_id']);
    $amount     = floatval($_POST['amount']);
    $date       = $_POST['date'];
    $status     = trim($_POST['status']);
    $notes      = trim($_POST['notes']);
    $stmt = $conn->prepare("INSERT INTO bills (patient_id, amount, date, status, notes) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $patient_id, $amount, $date, $status, $notes);
    if ($stmt->execute()) {
        $message = "Bill added successfully.";
    } else {
        $message = "Error adding bill: " . $stmt->error;
    }
    $stmt->close();
}

// Handle updating a bill
if (isset($_POST['update_bill'])) {
    $id        = intval($_POST['id']);
    $patient_id = intval($_POST['patient_id']);
    $amount    = floatval($_POST['amount']);
    $date      = $_POST['date'];
    $status    = trim($_POST['status']);
    $notes     = trim($_POST['notes']);
    $stmt = $conn->prepare("UPDATE bills SET patient_id=?, amount=?, date=?, status=?, notes=? WHERE id=?");
    $stmt->bind_param("idsssi", $patient_id, $amount, $date, $status, $notes, $id);
    if ($stmt->execute()) {
        $message = "Bill updated successfully.";
    } else {
        $message = "Error updating bill: " . $stmt->error;
    }
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM bills WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: billing.php');
    exit();
}

// Determine edit mode
$edit_mode = false;
$edit_bill = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM bills WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $edit_bill = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all bills with patient names
$bills_res = $conn->query("SELECT b.*, p.name AS patient_name FROM bills b JOIN patients p ON b.patient_id = p.id ORDER BY b.id DESC");
?>
<?php include 'header.php'; ?>
<h2 class="mb-4">Manage Billing</h2>
<?php if($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <?php echo $edit_mode ? 'Edit Bill' : 'Add New Bill'; ?>
    </div>
    <div class="card-body">
        <form method="post" action="billing.php">
            <?php if($edit_mode): ?>
                <input type="hidden" name="update_bill" value="1">
                <input type="hidden" name="id" value="<?php echo $edit_bill['id']; ?>">
            <?php else: ?>
                <input type="hidden" name="add_bill" value="1">
            <?php endif; ?>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Patient</label>
                    <select name="patient_id" class="form-control" required>
                        <option value="">-- Select Patient --</option>
                        <?php foreach($patients as $pid => $pname): ?>
                            <option value="<?php echo $pid; ?>" <?php if($edit_mode && $edit_bill['patient_id']==$pid) echo 'selected'; ?>><?php echo htmlspecialchars($pname); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_bill['amount']) : ''; ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="<?php echo $edit_mode ? htmlspecialchars($edit_bill['date']) : ''; ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Status</label>
                    <?php $st = $edit_mode ? $edit_bill['status'] : 'Unpaid'; ?>
                    <select name="status" class="form-control">
                        <option value="Unpaid" <?php echo ($st=='Unpaid')?'selected':''; ?>>Unpaid</option>
                        <option value="Paid" <?php echo ($st=='Paid')?'selected':''; ?>>Paid</option>
                        <option value="Pending" <?php echo ($st=='Pending')?'selected':''; ?>>Pending</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Notes</label>
                <textarea name="notes" class="form-control" rows="2"><?php echo $edit_mode ? htmlspecialchars($edit_bill['notes']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Add'; ?> Bill</button>
            <?php if($edit_mode): ?>
                <a href="billing.php" class="btn btn-secondary ml-2">Cancel</a>
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
                <th>Amount</th>
                <th>Date</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $bills_res->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                <td><?php echo htmlspecialchars($row['date']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['notes']); ?></td>
                <td>
                    <a href="billing.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-info">Edit</a>
                    <a href="billing.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this bill?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>