
<?php
include("../includes/auth.php");
include("../includes/db.php");

$teacher_id = $_GET['teacher_id'] ?? 0;

// Fetch existing teacher info
$stmt = $conn->prepare("
    SELECT t.*, u.username, u.email 
    FROM teachers t
    JOIN users u ON t.user_id = u.user_id
    WHERE t.teacher_id = ?
");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    die("Teacher not found.");
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $department = $_POST['department'];
    $phone = $_POST['phone'];

    // Update users table
    $stmt = $conn->prepare("UPDATE users SET username=?, email=? WHERE user_id=?");
    $stmt->bind_param("ssi", $username, $email, $teacher['user_id']);
    $stmt->execute();

    // Update teachers table
    $stmt2 = $conn->prepare("UPDATE teachers SET first_name=?, last_name=?, department=?, phone=? WHERE teacher_id=?");
    $stmt2->bind_param("ssssi", $first_name, $last_name, $department, $phone, $teacher_id);
    $stmt2->execute();

    header("Location: dashboard.php?msg=Teacher Updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Teacher</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="navigation">
        <h2>Update Teacher</h2>
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <div class="form-container">
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="<?= $teacher['username']; ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= $teacher['email']; ?>" required>

            <label>First Name</label>
            <input type="text" name="first_name" value="<?= $teacher['first_name']; ?>" required>

            <label>Last Name</label>
            <input type="text" name="last_name" value="<?= $teacher['last_name']; ?>" required>

            <label>Department</label>
            <input type="text" name="department" value="<?= $teacher['department']; ?>" required>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= $teacher['phone']; ?>" required>

            <button type="submit" class="btn btn-primary">Update Teacher</button>
        </form>
    </div>
</body>
</html>
