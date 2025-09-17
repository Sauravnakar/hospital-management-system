<?php
// User login page. For non-admin users.
session_start();
require '../db.php';

// Redirect to user dashboard if already logged in as regular user
if (isset($_SESSION['user_id']) && empty($_SESSION['is_admin'])) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $hash     = md5($password);
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hash);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id);
        $stmt->fetch();
        if ($username === 'admin') {
            // Disallow admin login via user page
            $message = 'Access denied. Please use the admin login page.';
        } else {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = false;
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
    <title>User Login - Hospital Management System</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">User Login</h2>
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
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        <p>Admin? <a href="../admin/login.php">Login here</a>.</p>
    </div>
</div>
</body>
</html>