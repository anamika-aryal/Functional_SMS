<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Student (role_id = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch student data
$stmt = $conn->prepare("
    SELECT s.*, u.username, u.email, u.profile_photo
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student record not found.");
}

$success = $error = "";

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $profile_photo = $student['profile_photo'];

    // Handle file upload if a new profile photo is uploaded
    if (!empty($_FILES['profile_photo']['name'])) {
        $file_name = basename($_FILES['profile_photo']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg','jpeg','png','gif'];

        if (in_array($file_ext, $allowed_ext)) {
            $upload_dir = "../uploads/profile_photos/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = "student_{$student['student_id']}_" . time() . "." . $file_ext;
            $file_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $file_path)) {
                $profile_photo = $new_filename;
                // Update user profile photo
                $stmt_photo = $conn->prepare("UPDATE users SET profile_photo=? WHERE user_id=?");
                $stmt_photo->bind_param("si", $profile_photo, $user_id);
                $stmt_photo->execute();
            } else {
                $error = "Failed to upload profile photo.";
            }
        } else {
            $error = "Invalid file type for profile photo. Allowed: jpg, jpeg, png, gif.";
        }
    }

    if (!$error) {
        // Update phone and address
        $stmt_update = $conn->prepare("UPDATE students SET phone=?, address=? WHERE user_id=?");
        $stmt_update->bind_param("ssi", $phone, $address, $user_id);
        $stmt_update->execute();

        $success = "Profile updated successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="navigation">
        <h2>My Profile</h2>
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
        <h3>👤 Profile Information</h3>

        <?php if ($success): ?>
            <p style="color:green;"><?= $success ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Username</label>
            <input type="text" value="<?= $student['username'] ?>" disabled>

            <label>Email</label>
            <input type="email" value="<?= $student['email'] ?>" disabled>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= $student['phone'] ?>" required>

            <label>Address</label>
            <textarea name="address" required><?= $student['address'] ?></textarea>

            <label>Profile Photo</label>
            <input type="file" name="profile_photo">
            <?php if ($student['profile_photo']): ?>
                <p>Current: <img src="../uploads/profile_photos/<?= $student['profile_photo'] ?>" width="80" style="border-radius:50%;"></p>
            <?php endif; ?>

            <button class="btn btn-primary" type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
