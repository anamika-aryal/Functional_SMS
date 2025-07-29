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

// Validate course_id
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Check if course belongs to this teacher
$course = $conn->query("
    SELECT * FROM courses
    WHERE course_id = $course_id AND instructor_id = $teacher_id
")->fetch_assoc();

if (!$course) {
    die("Invalid course or unauthorized access.");
}

// Get students enrolled in this course
$students = $conn->query("
    SELECT st.student_id, st.first_name, st.last_name
    FROM enrollments e
    JOIN students st ON e.student_id = st.student_id
    WHERE e.course_id = $course_id
    ORDER BY st.first_name
");

$success = $error = "";

// Handle attendance submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['attendance'])) {
    $date = $_POST['date'] ?? date('Y-m-d');

    foreach ($_POST['attendance'] as $student_id => $status) {
        $status = ($status == "Present") ? "Present" : "Absent";

        // Insert or update attendance
        $stmt = $conn->prepare("
            INSERT INTO attendance (student_id, course_id, date, status)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status = VALUES(status)
        ");
        $stmt->bind_param("iiss", $student_id, $course_id, $date, $status);
        $stmt->execute();
    }

    $success = "Attendance marked successfully for $date.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>
    <div class="navigation">
        <h2>Mark Attendance - <?= $course['course_name'] ?></h2>
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
        <h3>📅 Mark Attendance for <?= $course['course_name'] ?></h3>

        <?php if ($success): ?>
            <p style="color:green;"><?= $success ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="date">Select Date</label>
            <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>

            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Status</th>
                </tr>
                <?php if ($students->num_rows > 0): ?>
                    <?php while ($st = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $st['first_name'] . " " . $st['last_name'] ?></td>
                            <td>
                                <select name="attendance[<?= $st['student_id'] ?>]">
                                    <option value="Present">Present</option>
                                    <option value="Absent">Absent</option>
                                </select>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">No students enrolled in this course.</td></tr>
                <?php endif; ?>
            </table>

            <button type="submit" class="btn btn-primary" style="margin-top:15px;">Save Attendance</button>
        </form>
    </div>
</body>
</html>
