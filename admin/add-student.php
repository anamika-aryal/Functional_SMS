
<?php
include("../includes/auth.php");
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    // Create user account
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, is_approved) VALUES (?,?,?,?,1)");
    $role_id = 3; // Student
    $stmt->bind_param("sssi", $username, $email, $password, $role_id);
    $stmt->execute();
    $user_id = $stmt->insert_id;

    // Add student details
    $stmt2 = $conn->prepare("INSERT INTO students (user_id, first_name, last_name, dob, gender, address, phone, enrollment_date) VALUES (?,?,?,?,?,?,?,CURDATE())");
    $stmt2->bind_param("issssss", $user_id, $first_name, $last_name, $dob, $gender, $address, $phone);
    $stmt2->execute();

    header("Location: dashboard.php?msg=Student Added");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="navigation">
        <h2>Add Student</h2>
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

            <label>Date of Birth</label>
            <input type="date" name="dob" required>

            <label>Gender</label>
            <select name="gender" required>
                <option>Male</option>
                <option>Female</option>
            </select>

            <label>Address</label>
            <textarea name="address" required></textarea>

            <label>Phone</label>
            <input type="text" name="phone" required>

             <label>Course</label>
            <input type="text" name="phone" required>

             <label>Semester</label>
            <input type="text" name="phone" required>

            <button class="btn btn-primary" type="submit">Add Student</button>
        </form>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>

