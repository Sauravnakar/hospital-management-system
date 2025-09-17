<?php
// Admin login page. Only the administrator account should use this page.
session_start();
require '../db.php';

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['is_admin'])) {
    header("Location: dashboard.php");
    exit();
}

$message = '';
// Handle login submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hash     = md5($password);
    // Fetch the user with id and role to determine if they are an admin
    $stmt = $conn->prepare("SELECT id, role FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hash);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $role);
        $stmt->fetch();
        if ($role !== 'admin') {
            // Restrict non-admin accounts from logging in here
            $message = 'Access denied. Please use the user login page.';
        } else {
            // Valid admin login
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = true;
            header('Location: dashboard.php');
            exit();
        }
    } else {
        $message = 'Invalid username or password';
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Hospital Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Admin Login</h2>
    <?php if($message): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <div class="mt-3">
        <p>Not an admin? <a href="../user/login.php">User Login</a></p>
    </div>
</div>
</body>
</html>