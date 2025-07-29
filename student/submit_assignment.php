<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Student (role_id = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id
$student = $conn->query("SELECT student_id FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['student_id'];

// Validate assignment_id
$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;
$assignment = $conn->query("SELECT * FROM assignments WHERE assignment_id = $assignment_id")->fetch_assoc();

if (!$assignment) {
    die("Invalid Assignment ID.");
}

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_FILES["file"]["name"])) {
        $file_name = basename($_FILES["file"]["name"]);
        $file_tmp = $_FILES["file"]["tmp_name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Allowed file types
        $allowed_ext = ["pdf", "doc", "docx", "zip", "png", "jpg"];
        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Invalid file type. Allowed: pdf, doc, docx, zip, png, jpg";
        } else {
            $upload_dir = "../uploads/submissions/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Unique file name to avoid collisions
            $new_filename = "assignment_{$assignment_id}_student_{$student_id}_" . time() . "." . $file_ext;
            $file_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $file_path)) {
                // Insert or update submission
                $stmt = $conn->prepare("
                    INSERT INTO submissions (assignment_id, student_id, submission_date, status, grade, remarks)
                    VALUES (?, ?, NOW(), 'Submitted', NULL, NULL)
                    ON DUPLICATE KEY UPDATE 
                        submission_date = NOW(), status = 'Submitted'
                ");
                $stmt->bind_param("ii", $assignment_id, $student_id);
                $stmt->execute();

                $success = "Assignment submitted successfully!";
            } else {
                $error = "Error uploading file.";
            }
        }
    } else {
        $error = "Please select a file to submit.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Assignment</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="navigation">
        <h2>Submit Assignment</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>Submit: <?= $assignment['title'] ?></h3>
        <p><strong>Due Date:</strong> <?= $assignment['due_date'] ?></p>
        <p><strong>Description:</strong> <?= $assignment['description'] ?></p>

        <?php if ($success): ?>
            <p style="color:green;"><?= $success ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Upload File:</label>
            <input type="file" name="file" required>
            <button class="btn btn-primary" type="submit">Submit Assignment</button>
        </form>
    </div>
</body>
</html>
