<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Role-based access: only admin
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="navigation">
        <h2>Admin Dashboard</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="add-student.php">Add Student</a>
            <a href="add-teacher.php">Add Teacher</a>
            <a href="add-course.php">Add Course</a>
            <a href="add-event.php">Add Event</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="container">