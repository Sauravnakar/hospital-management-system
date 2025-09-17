<?php
// Page for regular users to book new appointments.  Users provide patient details
// and select a doctor, date and time for the appointment.  The patient will
// be created if not already existing.
session_start();
// Restrict page to logged-in non-admin users
if (!isset($_SESSION['user_id']) || !empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}

require '../db.php';

// Fetch doctors for dropdown
$doctorsResult = $conn->query("SELECT id, name FROM doctors ORDER BY name");

$message = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input
    $p_name    = trim($_POST['patient_name'] ?? '');
    $p_email   = trim($_POST['patient_email'] ?? '');
    $p_phone   = trim($_POST['patient_phone'] ?? '');
    $p_gender  = trim($_POST['patient_gender'] ?? '');
    $p_dob     = trim($_POST['patient_dob'] ?? '');
    $p_address = trim($_POST['patient_address'] ?? '');
    $doctor_id = intval($_POST['doctor_id'] ?? 0);
    $app_date  = trim($_POST['appointment_date'] ?? '');
    $app_time  = trim($_POST['appointment_time'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // Validate required fields
    if (empty($p_name) || empty($doctor_id) || empty($app_date) || empty($app_time)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Find existing patient by name and email
        $stmt = $conn->prepare("SELECT id FROM patients WHERE name = ? AND email = ? LIMIT 1");
        $stmt->bind_param('ss', $p_name, $p_email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($patient_id);
            $stmt->fetch();
        } else {
            // Insert new patient
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO patients (name, email, phone, gender, dob, address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssss', $p_name, $p_email, $p_phone, $p_gender, $p_dob, $p_address);
            if ($stmt->execute()) {
                $patient_id = $stmt->insert_id;
            } else {
                $error = 'Error creating patient: ' . $stmt->error;
            }
        }
        $stmt->close();

        // If no error and patient_id set, proceed to insert appointment
        if (empty($error) && isset($patient_id)) {
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, status, description) VALUES (?, ?, ?, ?, 'Scheduled', ?)");
            $stmt->bind_param('iisss', $patient_id, $doctor_id, $app_date, $app_time, $description);
            if ($stmt->execute()) {
                $message = 'Appointment scheduled successfully!';
                // Clear form values
                $p_name = $p_email = $p_phone = $p_gender = $p_dob = $p_address = $description = '';
                $doctor_id = 0;
                $app_date = $app_time = '';
            } else {
                $error = 'Error scheduling appointment: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

include 'header.php';
?>
<h2 class="mb-4">Book Appointment</h2>
<?php if ($message): ?>
    <div class="alert alert-success"><?php echo $message; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<form method="post" action="book_appointment.php">
    <h4>Patient Information</h4>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="patient_name">Name<span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="patient_name" name="patient_name" value="<?php echo htmlspecialchars($p_name ?? ''); ?>" required>
        </div>
        <div class="form-group col-md-6">
            <label for="patient_email">Email</label>
            <input type="email" class="form-control" id="patient_email" name="patient_email" value="<?php echo htmlspecialchars($p_email ?? ''); ?>">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="patient_phone">Phone</label>
            <input type="text" class="form-control" id="patient_phone" name="patient_phone" value="<?php echo htmlspecialchars($p_phone ?? ''); ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="patient_gender">Gender</label>
            <select class="form-control" id="patient_gender" name="patient_gender">
                <option value=""<?php echo empty($p_gender) ? ' selected' : ''; ?>>Select Gender</option>
                <option value="Male"<?php echo ($p_gender ?? '') === 'Male' ? ' selected' : ''; ?>>Male</option>
                <option value="Female"<?php echo ($p_gender ?? '') === 'Female' ? ' selected' : ''; ?>>Female</option>
                <option value="Other"<?php echo ($p_gender ?? '') === 'Other' ? ' selected' : ''; ?>>Other</option>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="patient_dob">Date of Birth</label>
            <input type="date" class="form-control" id="patient_dob" name="patient_dob" value="<?php echo htmlspecialchars($p_dob ?? ''); ?>">
        </div>
        <div class="form-group col-md-6">
            <label for="patient_address">Address</label>
            <textarea class="form-control" id="patient_address" name="patient_address" rows="3"><?php echo htmlspecialchars($p_address ?? ''); ?></textarea>
        </div>
    </div>

    <h4>Appointment Details</h4>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="doctor_id">Doctor<span class="text-danger">*</span></label>
            <select class="form-control" id="doctor_id" name="doctor_id" required>
                <option value="">Select Doctor</option>
                <?php if ($doctorsResult && $doctorsResult->num_rows > 0): ?>
                    <?php while ($doc = $doctorsResult->fetch_assoc()): ?>
                        <option value="<?php echo $doc['id']; ?>"<?php echo ($doctor_id ?? 0) == $doc['id'] ? ' selected' : ''; ?>><?php echo htmlspecialchars($doc['name']); ?></option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group col-md-3">
            <label for="appointment_date">Date<span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="appointment_date" name="appointment_date" value="<?php echo htmlspecialchars($app_date ?? ''); ?>" required>
        </div>
        <div class="form-group col-md-3">
            <label for="appointment_time">Time<span class="text-danger">*</span></label>
            <input type="time" class="form-control" id="appointment_time" name="appointment_time" value="<?php echo htmlspecialchars($app_time ?? ''); ?>" required>
        </div>
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Schedule Appointment</button>
</form>

<?php include 'footer.php'; ?>