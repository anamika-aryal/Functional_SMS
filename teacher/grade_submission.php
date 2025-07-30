<?php
session_start();
require_once("../includes/db.php");

// Make sure teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = intval($_POST['submission_id']);
    $grade = trim($_POST['grade']);
    $remarks = trim($_POST['remarks']);

    // Update submission record
    $sql = "UPDATE submissions SET grade = ?, remarks = ? WHERE submission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $grade, $remarks, $submission_id);

    if ($stmt->execute()) {
        header("Location: view_submissions.php?success=1");
        exit();
    } else {
        echo "Error updating grade: " . $conn->error;
    }
}
?>
