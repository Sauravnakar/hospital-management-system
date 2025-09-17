<?php
// Landing page for the Hospital Management System.  Displays options to navigate
// to either the administrator or user areas.  Users who are already logged in
// are automatically redirected to the correct dashboard based on their role.
session_start();

// Redirect logged-in users to the appropriate dashboard.
if (isset($_SESSION['user_id'])) {
    if (!empty($_SESSION['is_admin'])) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: user/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System</title>
    <!-- Bootstrap CSS for responsive layout -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="#">Hospital Management</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
</nav>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="mb-4">Welcome to the Hospital Management System</h1>
            <p class="lead">Select one of the portals below to proceed.</p>
        </div>
    </div>
    <div class="row mt-4">
        <!-- User Portal -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="card-title">User Portal</h3>
                    <p class="card-text">For patients and staff members who need to schedule appointments, view medical records, or manage their profile.</p>
                    <a href="user/login.php" class="btn btn-primary m-2">User Login</a>
                    <a href="user/register.php" class="btn btn-success m-2">User Register</a>
                </div>
            </div>
        </div>
        <!-- Admin Portal -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <h3 class="card-title">Administrator Portal</h3>
                    <p class="card-text">For administrators to manage departments, doctors, patients, appointments, billing and records.</p>
                    <a href="admin/login.php" class="btn btn-primary m-2">Admin Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>