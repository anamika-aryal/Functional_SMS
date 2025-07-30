<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Teacher (role_id = 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get teacher_id
$teacher = $conn->query("SELECT teacher_id FROM teachers WHERE user_id = $user_id")->fetch_assoc();
$teacher_id = $teacher['teacher_id'];

// Selected course
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch teacher's courses
$courses = $conn->query("
    SELECT course_id, course_name, course_code
    FROM courses
    WHERE instructor_id = $teacher_id
");

// Fetch attendance for selected course
$attendance_records = [];
if ($course_id) {
    $attendance_records = $conn->query("
        SELECT a.date, a.status, st.first_name, st.last_name
        FROM attendance a
        JOIN students st ON a.student_id = st.student_id
        WHERE a.course_id = $course_id
        ORDER BY a.date DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Management</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>
    <div class="navigation">
        <h2>Attendance Management</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="add_result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- Courses List -->
    <div class="card">
        <h3>📚 My Courses</h3>
        <table>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Actions</th>
            </tr>
            <?php if ($courses->num_rows > 0): ?>
                <?php while ($c = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?= $c['course_code'] ?></td>
                        <td><?= $c['course_name'] ?></td>
                        <td>
                            <a href="attendance.php?course_id=<?= $c['course_id'] ?>">View Attendance</a> |
                            <a href="mark_attendance.php?course_id=<?= $c['course_id'] ?>">Mark Attendance</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">No courses assigned.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Detailed Attendance -->
    <?php if ($course_id): ?>
    <div class="card">
        <h3>📅 Attendance Records for Selected Course</h3>
        <table>
            <tr>
                <th>Date</th>
                <th>Student Name</th>
                <th>Status</th>
            </tr>
            <?php if ($attendance_records->num_rows > 0): ?>
                <?php while ($row = $attendance_records->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['date'] ?></td>
                        <td><?= $row['first_name'] . " " . $row['last_name'] ?></td>
                        <td style="color:<?= ($row['status']=='Present') ? 'green':'red' ?>;">
                            <?= $row['status'] ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">No attendance records yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
    <?php endif; ?>
    <!-- Footer -->
     <?php include("includes/footer.php"); ?>
</body>
</html>
