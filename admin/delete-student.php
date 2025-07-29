
<?php
include("../includes/auth.php");
include("../includes/db.php");

$student_id = $_GET['student_id'] ?? 0;

if ($student_id > 0) {
    // Get the associated user_id first
    $stmt = $conn->prepare("SELECT user_id FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $user_id = $result['user_id'];

        // Delete from students table
        $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();

        // Delete from users table
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}

header("Location: dashboard.php?msg=Student Deleted");
exit;
?>
