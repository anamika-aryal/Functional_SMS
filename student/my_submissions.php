<?php
session_start();
require_once("../includes/db.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$student = $conn->query("SELECT student_id,first_name,last_name FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['student_id'];

$sql = "
    SELECT a.title, a.due_date, s.file_path, s.grade, s.submission_date
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.assignment_id
    WHERE s.student_id = ?
    ORDER BY a.due_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Submissions</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
     <div class="navigation">
    <h2>Welcome, <?= $student['first_name'] ?> <?= $student['last_name']?> </h2>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="assignments.php">Assignments</a>
        <a href="my_submissions.php">My Submissions</a> 
        <a href="attendance.php">Attendance</a>
        <a href="result.php">Results</a>
        <a href="profile_setup.php">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>
<div class="card">
    <h3>📄 My Assignment Submissions</h3>
    <table>
        <tr>
            <th>Assignment</th>
            <th>Due Date</th>
            <th>Submitted On</th>
            <th>File</th>
            <th>Grade</th>

        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= date("d M Y", strtotime($row['due_date'])) ?></td>
                    <td><?= date("d M Y", strtotime($row['submission_date'])) ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a href="../<?= htmlspecialchars($row['file_path']) ?>" target="_blank">Download</a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?= $row['grade'] ?: '-' ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">No submissions found.</td></tr>
        <?php endif; ?>
    </table>
</div>
<?php include("./includes/footer.php"); ?>
</body>
</html>
