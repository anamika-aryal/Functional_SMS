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

// Validate assignment_id
$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

// Fetch assignment details and ensure teacher owns it
$assignment = $conn->query("
    SELECT a.*, c.course_name 
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    WHERE a.assignment_id = $assignment_id AND c.instructor_id = $teacher_id
")->fetch_assoc();

if (!$assignment) {
    die("Invalid assignment or you are not authorized to view it.");
}

// Handle grading form submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['submission_id'])) {
    $submission_id = intval($_POST['submission_id']);
    $grade = $_POST['grade'];
    $remarks = $_POST['remarks'];

    $stmt = $conn->prepare("UPDATE submissions SET grade=?, remarks=? WHERE submission_id=?");
    $stmt->bind_param("ssi", $grade, $remarks, $submission_id);
    $stmt->execute();
}

// Fetch student submissions
$submissions = $conn->query("
    SELECT s.submission_id, s.student_id, s.submission_date, s.status, s.grade, s.remarks,
           st.first_name, st.last_name, u.email
    FROM submissions s
    JOIN students st ON s.student_id = st.student_id
    JOIN users u ON st.user_id = u.user_id
    WHERE s.assignment_id = $assignment_id
    ORDER BY s.submission_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Submissions</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>
    <div class="navigation">
        <h2>Assignment Submissions</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="add_result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>📄 Submissions for: <?= $assignment['title'] ?> (<?= $assignment['course_name'] ?>)</h3>
        <p><strong>Due Date:</strong> <?= $assignment['due_date'] ?></p>
        <p><strong>Description:</strong> <?= $assignment['description'] ?></p>

        <table>
            <tr>
                <th>Student</th>
                <th>Email</th>
                <th>Submission Date</th>
                <th>Status</th>
                <th>Grade</th>
                <th>Remarks</th>
                <th>Actions</th>
            </tr>
            <?php if ($submissions->num_rows > 0): ?>
                <?php while ($row = $submissions->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['first_name'] . " " . $row['last_name'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['submission_date'] ?></td>
                        <td style="color: <?= ($row['status']=='Submitted') ? 'green':'red' ?>;">
                            <?= $row['status'] ?>
                        </td>
                        <td><?= $row['grade'] ?? 'N/A' ?></td>
                        <td><?= $row['remarks'] ?? 'N/A' ?></td>
                        <td>
                            <form method="POST" style="display:flex; flex-direction:column; gap:5px;">
                                <input type="hidden" name="submission_id" value="<?= $row['submission_id'] ?>">
                                <input type="text" name="grade" placeholder="Grade" value="<?= $row['grade'] ?? '' ?>">
                                <input type="text" name="remarks" placeholder="Remarks" value="<?= $row['remarks'] ?? '' ?>">
                                <button class="btn btn-primary" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No submissions yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
