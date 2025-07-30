<?php
session_start();
require_once("../includes/db.php");

// Ensure student is logged in (role_id = 3 assumed)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id
$studentData = $conn->query("SELECT student_id FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $studentData['student_id'];

// Fetch assignments only for courses the student is enrolled in
$sql = "
    SELECT a.assignment_id, a.title, a.description, a.due_date, c.course_name, c.course_code,
           s.status AS submission_status, s.grade, s.submission_date
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON a.course_id = e.course_id
    LEFT JOIN submissions s 
           ON a.assignment_id = s.assignment_id AND s.student_id = ?
    WHERE e.student_id = ?
    ORDER BY a.due_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$assignments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assignments</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="navigation">
        <h2>My Assignments</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php" class="btn btn-primary">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>📚 Assignments</h3>

        <?php if ($assignments->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $assignments->fetch_assoc()): ?>
                        <?php
                        // Determine assignment status
                        if ($row['submission_status'] === null) {
                            $status = "<span style='color:red;'>Not Submitted</span>";
                        } elseif ($row['grade'] === null || $row['grade'] === '') {
                            $status = "<span style='color:orange;'>Submitted</span>";
                        } else {
                            $status = "<span style='color:green;'>Graded ({$row['grade']})</span>";
                        }

                        // Highlight overdue if not submitted
                        $dueClass = "";
                        if (strtotime($row['due_date']) < time() && $row['submission_status'] === null) {
                            $dueClass = "style='color:red; font-weight:bold;'";
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['course_code'] . " - " . $row['course_name']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td <?= $dueClass ?>><?= date("d M Y", strtotime($row['due_date'])) ?></td>
                            <td><?= $status ?></td>
                            <td>
                                <?php if ($row['submission_status'] === null && strtotime($row['due_date']) >= time()): ?>
                                    <a href="submit_assignment.php?id=<?= $row['assignment_id'] ?>" class="btn btn-primary">Submit</a>
                                <?php elseif ($row['submission_status'] !== null): ?>
                                    <a href="my_submissions.php?id=<?= $row['assignment_id'] ?>" class="btn">View</a>
                                <?php else: ?>
                                    <span style="color:gray;">Closed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No assignments found.</p>
        <?php endif; ?>
    </div>
<?php include("./includes/footer.php"); ?>
</body>
</html>
