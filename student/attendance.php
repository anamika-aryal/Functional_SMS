<?php
session_start();
require_once("../includes/db.php");

// Ensure student is logged in (role_id = 3 assumed)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id from logged-in user
$studentData = $conn->query("SELECT student_id FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $studentData['student_id'];

// Fetch attendance per course
$sql = "
    SELECT c.course_code, c.course_name,
           SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) AS present_days,
           COUNT(a.attendance_id) AS total_days
    FROM attendance a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON c.course_id = e.course_id
    WHERE a.student_id = ? AND e.student_id = ?
    GROUP BY c.course_id
    ORDER BY c.course_name
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$attendanceData = $stmt->get_result();
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
            <a href="attendance.php" class="btn btn-primary">Attendance</a>
            <a href="result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>📅 Attendance Overview</h3>

        <?php if ($attendanceData->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Present Days</th>
                        <th>Total Days</th>
                        <th>Attendance %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $attendanceData->fetch_assoc()): ?>
                        <?php
                        $percentage = $row['total_days'] > 0 
                                      ? round(($row['present_days'] / $row['total_days']) * 100, 2) 
                                      : 0;

                        // Highlight low attendance
                        $percentStyle = ($percentage < 75) 
                                        ? "style='color:red; font-weight:bold;'" 
                                        : "style='color:green;'";
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= $row['present_days'] ?></td>
                            <td><?= $row['total_days'] ?></td>
                            <td <?= $percentStyle ?>><?= $percentage ?>%</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No attendance records found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
