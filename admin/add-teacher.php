
<?php
include("../includes/auth.php");
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $department = $_POST['department'];
    $phone = $_POST['phone'];

    // Create user account with role = Teacher
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, is_approved) VALUES (?,?,?,?,1)");
    $role_id = 2; // Teacher
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // Add teacher details
    $stmt2 = $conn->prepare("INSERT INTO teachers (user_id, first_name, last_name, department, phone) VALUES (?,?,?,?,?)");
    $stmt2->bind_param("issss", $user_id, $first_name, $last_name, $department, $phone);
    $stmt2->execute();

    header("Location: dashboard.php?msg=Teacher Added");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Teacher</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include("./includes/header.php"); ?>
    <div class="navigation">
        <h2>Add Teacher</h2>
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <div class="form-container">
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" required>
            
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>First Name</label>
            <input type="text" name="first_name" required>

            <label>Last Name</label>
            <input type="text" name="last_name" required>

            <label>Department</label>
            <input type="text" name="department" required>

            <label>Phone</label>
            <input type="text" name="phone" required>

            <button class="btn btn-primary" type="submit">Add Teacher</button>
        </form>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
