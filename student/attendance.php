<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Student (role_id = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id
$student = $conn->query("SELECT student_id FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch overall attendance percentage per course
$overall_attendance = $conn->query("
    SELECT c.course_name,
           SUM(a.status = 'Present') AS present_days,
           COUNT(*) AS total_days,
           ROUND((SUM(a.status = 'Present') / COUNT(*)) * 100, 2) AS attendance_percentage
    FROM attendance a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.student_id = $student_id
    GROUP BY c.course_id
");

// Fetch detailed daily attendance
$daily_attendance = $conn->query("
    SELECT c.course_name, a.date, a.status
    FROM attendance a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.student_id = $student_id
    ORDER BY a.date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Attendance</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="navigation">
        <h2>My Attendance</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- Overall Attendance -->
    <div class="card">
        <h3>📊 Overall Attendance by Course</h3>
        <table>
            <tr>
                <th>Course</th>
                <th>Present Days</th>
                <th>Total Days</th>
                <th>Attendance %</th>
            </tr>
            <?php
            if ($overall_attendance->num_rows > 0) {
                while ($row = $overall_attendance->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['course_name']}</td>
                            <td>{$row['present_days']}</td>
                            <td>{$row['total_days']}</td>
                            <td>{$row['attendance_percentage']}%</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No attendance records found.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Detailed Daily Attendance -->
    <div class="card">
        <h3>📅 Daily Attendance Records</h3>
        <table>
            <tr>
                <th>Date</th>
                <th>Course</th>
                <th>Status</th>
            </tr>
            <?php
            if ($daily_attendance->num_rows > 0) {
                while ($row = $daily_attendance->fetch_assoc()) {
                    $statusColor = ($row['status'] == 'Present') ? "green" : "red";
                    echo "<tr>
                            <td>{$row['date']}</td>
                            <td>{$row['course_name']}</td>
                            <td style='color:$statusColor;'>{$row['status']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No attendance records found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
