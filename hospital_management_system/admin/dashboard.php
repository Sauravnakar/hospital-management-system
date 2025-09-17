<?php
// Admin dashboard page showing summary counts.
session_start();
// Only allow access if logged in as admin
if(!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}

require '../db.php';

// Fetch counts for dashboard widgets
$patient_count     = $conn->query("SELECT COUNT(*) AS count FROM patients")->fetch_assoc()['count'] ?? 0;
$doctor_count      = $conn->query("SELECT COUNT(*) AS count FROM doctors")->fetch_assoc()['count'] ?? 0;
$appointment_count = $conn->query("SELECT COUNT(*) AS count FROM appointments")->fetch_assoc()['count'] ?? 0;
$department_count  = $conn->query("SELECT COUNT(*) AS count FROM departments")->fetch_assoc()['count'] ?? 0;
$billing_count     = $conn->query("SELECT COUNT(*) AS count FROM bills")->fetch_assoc()['count'] ?? 0;
$record_count      = $conn->query("SELECT COUNT(*) AS count FROM records")->fetch_assoc()['count'] ?? 0;
?>
<?php include 'header.php'; ?>
<h2 class="mb-4">Admin Dashboard</h2>
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-header">Patients</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $patient_count; ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-header">Doctors</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $doctor_count; ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-info">
            <div class="card-header">Appointments</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $appointment_count; ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-header">Departments</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $department_count; ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-danger">
            <div class="card-header">Bills</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $billing_count; ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-secondary">
            <div class="card-header">Records</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $record_count; ?></h5>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>