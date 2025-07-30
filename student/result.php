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
$student = $conn->query("SELECT student_id, first_name FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch student results
$results = $conn->query("
    SELECT r.*, c.course_name, c.course_code
    FROM results r
    JOIN courses c ON r.course_id = c.course_id
    WHERE r.student_id = $student_id
    ORDER BY r.course_id
");

// Fetch SGPA/CGPA
$metrics = $conn->query("
    SELECT sgpa, cgpa FROM quick_view_metrics WHERE student_id = $student_id
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Results</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="navigation">
        <h2><?= $student['first_name']?>'s Results</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <h3>📊 Course Results</h3>
        <table>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Internal Marks</th>
                <th>External Marks</th>
                <th>Total Marks</th>
                <th>Letter Grade</th>
                <th>Grade Point</th>
                <th>Exam Type</th>
            </tr>
            <?php
            if ($results->num_rows > 0) {
                while ($row = $results->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['course_code']}</td>
                            <td>{$row['course_name']}</td>
                            <td>{$row['internal_marks']}</td>
                            <td>{$row['external_marks']}</td>
                            <td>{$row['total_marks']}</td>
                            <td>{$row['letter_grade']}</td>
                            <td>{$row['grade_point']}</td>
                            <td>{$row['exam_type']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No results found.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- GPA Summary -->
    <div class="card">
        <h3>🎓 GPA Summary</h3>
        <p>SGPA: <strong><?= $metrics['sgpa'] ?? 'N/A' ?></strong></p>
        <p>CGPA: <strong><?= $metrics['cgpa'] ?? 'N/A' ?></strong></p>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
