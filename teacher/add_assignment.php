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

$success = $error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $file_name = null;

    // Handle optional file upload
    if (!empty($_FILES['file']['name'])) {
        $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'doc', 'docx', 'zip', 'jpg', 'png'];

        if (!in_array($file_ext, $allowed_ext)) {
            $error = "Invalid file type. Allowed: pdf, doc, docx, zip, jpg, png.";
        } else {
            $upload_dir = "../uploads/assignments/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_name = "assignment_" . time() . "_teacher_" . $teacher_id . "." . $file_ext;
            $file_path = $upload_dir . $file_name;

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                $error = "File upload failed.";
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("
            INSERT INTO assignments (course_id, title, description, due_date, file)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("issss", $course_id, $title, $description, $due_date, $file_name);
        $stmt->execute();

        $success = "Assignment added successfully!";
    }
}

// Fetch teacher courses for dropdown
$courses = $conn->query("SELECT course_id, course_name FROM courses WHERE instructor_id = $teacher_id");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Assignment</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>
    <div class="navigation">
        <h2>Add Assignment</h2>
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
        <h3>➕ Create New Assignment</h3>

        <?php if ($success): ?>
            <p style="color:green;"><?= $success ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Course</label>
            <select name="course_id" required>
                <option value="">Select Course</option>
                <?php while ($row = $courses->fetch_assoc()): ?>
                    <option value="<?= $row['course_id'] ?>"><?= $row['course_name'] ?></option>
                <?php endwhile; ?>
            </select>

            <label>Title</label>
            <input type="text" name="title" required>

            <label>Description</label>
            <textarea name="description" required></textarea>

            <label>Due Date</label>
            <input type="datetime-local" name="due_date" required>

            <label>Attachment (Optional)</label>
            <input type="file" name="file">

            <button class="btn btn-primary" type="submit">Add Assignment</button>
        </form>
    </div>
</body>
</html>
