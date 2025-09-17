<?php
// Admin reset page. Allows an existing admin to create a new admin account or
// update the password of an existing admin.  Accessible only to logged-in
// admins.
session_start();
require '../db.php';

// Only allow access if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername     = trim($_POST['username'] ?? '');
    $newPassword     = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validate input
    if ($newUsername === '' || $newPassword === '' || $confirmPassword === '') {
        $error = 'Please fill in all fields.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare('SELECT id, role FROM users WHERE username = ?');
        $stmt->bind_param('s', $newUsername);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Fetch existing user details
            $stmt->bind_result($existingId, $existingRole);
            $stmt->fetch();
            if ($existingRole !== 'admin') {
                // Disallow updating non-admin accounts through this page
                $error = 'The username already exists and is not an admin.';
            } else {
                // Update password for existing admin
                $stmt->close();
                $newHash = md5($newPassword);
                $update  = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
                $update->bind_param('si', $newHash, $existingId);
                if ($update->execute()) {
                    $success = 'Admin credentials updated successfully.';
                } else {
                    $error = 'Error updating admin credentials: ' . $update->error;
                }
                $update->close();
            }
        } else {
            // Create a new admin account
            $stmt->close();
            $newHash = md5($newPassword);
            $insert  = $conn->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, "admin")');
            $insert->bind_param('ss', $newUsername, $newHash);
            if ($insert->execute()) {
                $success = 'New admin account created successfully.';
            } else {
                $error = 'Error creating admin account: ' . $insert->error;
            }
            $insert->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Management - Hospital Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Admin Account Management</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="post" action="adminreset.php">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Admin</button>
        <a href="dashboard.php" class="btn btn-secondary ml-2">Back to Dashboard</a>
    </form>
</div>
</body>
</html>