<?php
session_start();
require_once("../includes/db.php");

// Ensure student is logged in (role_id = 3 assumed)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id from session user_id
$studentData = $conn->query("SELECT student_id FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $studentData['student_id'];

// Fetch all results for the student, ordered by course + exam type
$sql = "
    SELECT r.*, c.course_name, c.course_code
    FROM results r
    JOIN courses c ON r.course_id = c.course_id
    WHERE r.student_id = ?
    ORDER BY c.course_name ASC, 
             FIELD(r.exam_type, 'UT', 'Pre-board', 'Board')
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$results = $stmt->get_result();
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
        <h2>My Exam Results</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="result.php" class="btn btn-primary">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>📊 Exam Results</h3>

        <?php if ($results->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Exam Type</th>
                        <th>Internal Marks</th>
                        <th>External Marks</th>
                        <th>Total Marks</th>
                        <th>Grade</th>
                        <th>Grade Point</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['course_code']) ?></td>
                            <td><?= htmlspecialchars($row['course_name']) ?></td>
                            <td><?= htmlspecialchars($row['exam_type']) ?></td>
                            <td><?= htmlspecialchars($row['internal_marks']) ?></td>
                            <td><?= htmlspecialchars($row['external_marks']) ?></td>
                            <td><?= htmlspecialchars($row['total_marks']) ?></td>
                            <td><?= htmlspecialchars($row['letter_grade']) ?></td>
                            <td><?= htmlspecialchars($row['grade_point']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
