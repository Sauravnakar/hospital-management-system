<?php
// User dashboard page. Shows a welcome message to non-admin users.
session_start();
// Redirect if not logged in or if admin
if (!isset($_SESSION['user_id']) || !empty($_SESSION['is_admin'])) {
    header('Location: ../index.php');
    exit();
}
?>
<?php
// Connect to DB first, then include header
require '../db.php';
include 'header.php';
// Get counts for summary display
$doctorCount = $conn->query("SELECT COUNT(*) AS cnt FROM doctors")->fetch_assoc()['cnt'] ?? 0;
$deptCount   = $conn->query("SELECT COUNT(*) AS cnt FROM departments")->fetch_assoc()['cnt'] ?? 0;
$appCount    = $conn->query("SELECT COUNT(*) AS cnt FROM appointments")->fetch_assoc()['cnt'] ?? 0;
?>

<h2 class="mb-4">User Dashboard</h2>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Doctors</h5>
                <p class="card-text display-4"><?php echo $doctorCount; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Departments</h5>
                <p class="card-text display-4"><?php echo $deptCount; ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info h-100">
            <div class="card-body">
                <h5 class="card-title">Appointments</h5>
                <p class="card-text display-4"><?php echo $appCount; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>!</h5>
        <p class="card-text">Use the navigation menu to view doctors and departments, schedule a new appointment, or review scheduled appointments.</p>
    </div>
</div>

<?php include 'footer.php'; ?>