<?php
// User registration page. Allows creation of new non-admin accounts.
session_start();
require '../db.php';

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    if (!empty($_SESSION['is_admin'])) {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username        = trim($_POST['username']);
    $password        = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    // Disallow reserved admin username
    if (strtolower($username) === 'admin') {
        $message = "The username 'admin' is reserved.";
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = 'Username already taken.';
        } else {
            $stmt->close();
            $hash = md5($password);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            if ($stmt->execute()) {
                // registration success
                header('Location: login.php?registered=1');
                exit();
            } else {
                $message = 'Error during registration: ' . $stmt->error;
            }
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
    <title>User Registration - Hospital Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Register</h2>
    <?php if($message): ?>
        <div class="alert alert-danger"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" action="register.php">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success">Register</button>
    </form>
    <div class="mt-3">
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
        <p>Admin? <a href="../admin/login.php">Admin Login</a>.</p>
    </div>
</div>
</body>
</html>