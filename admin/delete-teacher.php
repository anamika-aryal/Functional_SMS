
<?php
include("../includes/auth.php");
include("../includes/db.php");

$teacher_id = $_GET['teacher_id'] ?? 0;

if ($teacher_id > 0) {
    // Get associated user_id first
    $stmt = $conn->prepare("SELECT user_id FROM teachers WHERE teacher_id = ?");
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result) {
        $user_id = $result['user_id'];

        // Delete from teachers table
        $stmt = $conn->prepare("DELETE FROM teachers WHERE teacher_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();

        // Delete from users table
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}

header("Location: dashboard.php?msg=Teacher Deleted");
exit;
?>
