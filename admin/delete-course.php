<?php
include("../includes/auth.php");
include("../includes/db.php");

$success = $error = "";

if (!isset($_GET['id'])) {
    die("Course ID not provided.");
}

$course_id = intval($_GET['id']);

// Check if this course is mapped to a program
$map_check_stmt = $conn->prepare("SELECT * FROM program_courses WHERE course_id = ?");
$map_check_stmt->bind_param("i", $course_id);
$map_check_stmt->execute();
$map_result = $map_check_stmt->get_result();

$is_mapped = $map_result->num_rows > 0;

// Check if course has enrollments
$enrollment_stmt = $conn->prepare("SELECT * FROM enrollments WHERE course_id = ?");
$enrollment_stmt->bind_param("i", $course_id);
$enrollment_stmt->execute();
$enroll_result = $enrollment_stmt->get_result();

$has_enrollments = $enroll_result->num_rows > 0;

// Unmap or delete based on action
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST["action"];

    if ($action == "unmap") {
        $stmt = $conn->prepare("DELETE FROM program_courses WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        if ($stmt->execute()) {
            $success = "Course successfully unmapped from all programs/semesters.";
        } else {
            $error = "Failed to unmap course.";
        }
    } elseif ($action == "delete") {
        if ($is_mapped || $has_enrollments) {
            $error = "Cannot delete. Course is in use.";
        } else {
            $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
            $stmt->bind_param("i", $course_id);
            if ($stmt->execute()) {
                $success = "Course deleted successfully.";
            } else {
                $error = "Failed to delete course.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Course</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include("./includes/header.php"); ?>

<div class="container">
    <h2>Delete Course</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <p>This course is currently:
            <?php if ($is_mapped): ?>
                <strong>Mapped to a program/semester</strong><br>
            <?php endif; ?>
            <?php if ($has_enrollments): ?>
                <strong>Enrolled by students</strong><br>
            <?php endif; ?>
        </p>

        <label>Select action:</label><br>
        <select name="action" required>
            <option value="">-- Choose --</option>
            <option value="unmap">Unmap from programs/semesters</option>
            <option value="delete">Delete Course (only if unused)</option>
        </select><br><br>

        <button type="submit">Confirm Action</button>
    </form>
</div>

<?php include("./includes/footer.php"); ?>
</body>
</html>
