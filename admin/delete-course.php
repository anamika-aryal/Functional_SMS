
<?php
include("../includes/auth.php");
include("../includes/db.php");

$course_id = $_GET['course_id'] ?? 0;

if ($course_id > 0) {
    $stmt = $conn->prepare("DELETE FROM courses WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
}

header("Location: dashboard.php?msg=Course Deleted");
exit;
?>
