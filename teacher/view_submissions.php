<?php
session_start();
require_once("../includes/db.php");

// Make sure the user is logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$teacher = $conn->query("SELECT teacher_id FROM teachers WHERE user_id = $user_id")->fetch_assoc();
$teacher_id = $teacher['teacher_id'];


// Fetch submissions for assignments posted by this teacher
$sql = "
SELECT s.submission_id, s.submission_date, s.file_path, s.grade, s.remarks,
       st.first_name, st.last_name, a.title AS assignment_title
FROM assignments a
JOIN submissions s ON a.assignment_id = s.assignment_id
JOIN students st ON s.student_id = st.student_id
WHERE a.course_id IN (
    SELECT course_id FROM courses WHERE instructor_id = ?
)
ORDER BY s.submission_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Submissions</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>
<div class="navigation">
    <h2>Welcome, Teacher</h2>
    <div>
        <a href="dashboard.php">Dashboard</a>
        <a href="assignments.php">Assignments</a>
        <a href="attendance.php">Attendance</a>
        <a href="add_result.php">Results</a>
        <a href="profile_setup.php">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>

<div class="card">
    <h3>Submitted Assignments</h3>
    <table>
        <tr>
            <th>Student</th>
            <th>Assignment</th>
            <th>Submitted On</th>
            <th>File</th>
            <th>Grade</th>
            <th>Remarks</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['assignment_title']) ?></td>
                <td><?= htmlspecialchars($row['submission_date']) ?></td>
                <td>
                    <?php if (!empty($row['file_path'])) { ?>
                        <a href="../<?= htmlspecialchars($row['file_path']) ?>" target="_blank">View</a>
                    <?php } else { echo "No file"; } ?>
                </td>
                <td><?= htmlspecialchars($row['grade']) ?></td>
                <td><?= htmlspecialchars($row['remarks']) ?></td>
                <td>
                    <form action="grade_submission.php" method="POST">
                        <input type="hidden" name="submission_id" value="<?= $row['submission_id'] ?>">
                        <input type="text" name="grade" placeholder="Grade" value="<?= htmlspecialchars($row['grade']) ?>" required>
                        <input type="text" name="remarks" placeholder="Remarks" value="<?= htmlspecialchars($row['remarks']) ?>">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
<?php include("./includes/footer.php"); ?>
</body>
</html>
