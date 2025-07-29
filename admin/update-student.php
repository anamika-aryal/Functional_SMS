
<?php
include("../includes/auth.php");
include("../includes/db.php");

$student_id = $_GET['student_id'] ?? 0;

// Fetch existing student info
$stmt = $conn->prepare("
    SELECT s.*, u.username, u.email 
    FROM students s
    JOIN users u ON s.user_id = u.user_id
    WHERE s.student_id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Update users table
    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE user_id=?");
    $stmt->bind_param("ssi", $username, $email, $student['user_id']);
    $stmt->execute();

    // Update students table
    $stmt2 = $conn->prepare("UPDATE students SET first_name=?, last_name=?, dob=?, gender=?, address=?, phone=? WHERE student_id=?");
    $stmt2->bind_param("ssssssi", $first_name, $last_name, $dob, $gender, $address, $phone, $student_id);
    $stmt2->execute();

    header("Location: dashboard.php?msg=Student Updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Student</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <?php //include("../includes/header.php"); ?>

    <div class="form-container">
        <h3>Update Student</h3>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= $student['username']; ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= $student['email']; ?>" required>

            <label>First Name</label>
            <input type="text" name="first_name" value="<?= $student['first_name']; ?>" required>

            <label>Last Name</label>
            <input type="text" name="last_name" value="<?= $student['last_name']; ?>" required>

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?= $student['dob']; ?>" required>

            <label>Gender</label>
            <select name="gender" required>
                <option <?= $student['gender']=='Male'?'selected':''; ?>>Male</option>
                <option <?= $student['gender']=='Female'?'selected':''; ?>>Female</option>
            </select>

            <label>Address</label>
            <textarea name="address" required><?= $student['address']; ?></textarea>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= $student['phone']; ?>" required>

            <button type="submit" class="btn btn-primary">Update Student</button>
        </form>
    </div>
</body>
</html>
