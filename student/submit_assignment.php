<?php
session_start();
require_once("../includes/db.php");

// Role check: Student only
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id
$student = $conn->query("SELECT student_id FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['student_id'];

$assignment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $targetDir = "../uploads/submissions/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["file"]["name"]);
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        $filePath = "uploads/submissions/" . $fileName;

        $stmt = $conn->prepare("
            INSERT INTO submissions (assignment_id, student_id, file_path, submission_date) 
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE file_path = VALUES(file_path), submission_date = NOW()
        ");
        $stmt->bind_param("iis", $assignment_id, $student_id, $filePath);
        $stmt->execute();

        $success = "Assignment submitted successfully!";
    } else {
        $error = "File upload failed.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Assignment</title>
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
    <h3>Submit Assignment</h3>
    <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Upload File:</label>
        <input type="file" name="file" required>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>
<?php include("./includes/footer.php"); ?>
</body>
</html>
