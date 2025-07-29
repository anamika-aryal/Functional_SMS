<?php
session_start();
require_once("includes/db.php");

// If already logged in, redirect to respective dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role_id'])) {
    if ($_SESSION['role_id'] == 1) header("Location: admin/dashboard.php");
    elseif ($_SESSION['role_id'] == 2) header("Location: teacher/dashboard.php");
    elseif ($_SESSION['role_id'] == 3) header("Location: student/dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Fetch user
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role_id'] = $user['role_id'];

            // Redirect based on role
            switch ($user['role_id']) {
                case 1: header("Location: admin/dashboard.php"); break;
                case 2: header("Location: teacher/dashboard.php"); break;
                case 3: header("Location: student/dashboard.php"); break;
                default: $error = "Invalid role. Contact admin.";
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>🔐 Login</h2>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>
