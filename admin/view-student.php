<?php
include("../includes/auth.php");
include("../includes/db.php");

if (!isset($_GET['student_id'])) {
    die("Student ID is required.");
}
$student_id = intval($_GET['student_id']);

// Fetch student & user info
$stmt = $conn->prepare("
    SELECT s.*, u.username, u.email, p.program_name
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    JOIN programs p ON s.program_id = p.program_id
    WHERE s.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$student = $result->fetch_assoc()) {
    die("Student not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Student</title>
    <link rel="stylesheet" href="admin.css"> <!-- Optional: Your CSS -->
</head>
<body>
<?php include("./includes/header.php"); ?>

<div class="container">
    <h2>👨‍🎓 Student Profile</h2>
    
    <p><strong>Name:</strong> <?php echo $student['first_name'] . ' ' . $student['last_name']; ?></p>
    <p><strong>Username:</strong> <?php echo $student['username']; ?></p>
    <p><strong>Email:</strong> <?php echo $student['email']; ?></p>
    <p><strong>Phone:</strong> <?php echo $student['phone']; ?></p>
    <p><strong>Program:</strong> <?php echo $student['program_name']; ?></p>
    <p><strong>Semester:</strong> <?php echo $student['semester']; ?></p>

    <h3>📚 Enrolled Courses</h3>
    <table>
        <tr>
            <th>Course Code</th><th>Course Name</th><th>Credits</th>
        </tr>
        <?php
        $course_stmt = $conn->prepare("
            SELECT c.course_code, c.course_name, c.credits
            FROM enrollments e
            JOIN courses c ON e.course_id = c.course_id
            WHERE e.student_id = ?
            ORDER BY c.course_code
        ");
        $course_stmt->bind_param("i", $student_id);
        $course_stmt->execute();
        $courses = $course_stmt->get_result();

        if ($courses->num_rows > 0) {
            while ($course = $courses->fetch_assoc()) {
                echo "<tr>
                        <td>{$course['course_code']}</td>
                        <td>{$course['course_name']}</td>
                        <td>{$course['credits']}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No courses enrolled.</td></tr>";
        }
        ?>
    </table>

    <br>
    
</div>

<?php include("./includes/footer.php"); ?>
</body>
</html>
