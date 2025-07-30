<?php
include("./includes/auth.php");
include("./includes/db.php");
$success = $error = "";


// Fetch all programs for dropdown
$programs_result = $conn->query("SELECT * FROM programs");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $program_id = intval($_POST["program_id"]);
    $semester = intval($_POST["semester"]);

    // Basic validation
    if (!$first_name || !$last_name || !$email || !$username || !$program_id || !$semester) {
        $error = "Please fill in all required fields.";
    } else {
        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role_id, is_approved) VALUES (?, ?, ?, 3, 1)");
        $stmt->bind_param("sss", $username, $password, $email);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insert into students table
            $stmt2 = $conn->prepare("INSERT INTO students (user_id, first_name, last_name, dob, gender, address, phone, enrollment_date, program_id, semester) VALUES (?,?,?,?,?,?,?,CURDATE(),?,?)");
             $stmt2->bind_param("issssssss", $user_id, $first_name, $last_name, $dob, $gender, $address, $phone, $program_id, $semester);
            //$stmt2->execute();

            // $stmt2 = $conn->prepare("INSERT INTO students (user_id, first_name, last_name, phone, enrollment_date program_id, semester) VALUES (?, ?, ?, ?, CURDATE(), ?, ?)");
            // $stmt2->bind_param("isssii", $user_id, $first_name, $last_name, $phone, $program_id, $semester);
            if ($stmt2->execute()) {
                $student_id = $stmt2->insert_id;

                // Fetch courses from program_courses
                $course_q = $conn->prepare("SELECT course_id FROM program_courses WHERE program_id = ? AND semester = ?");
                $course_q->bind_param("ii", $program_id, $semester);
                $course_q->execute();
                $result = $course_q->get_result();

                // Auto-insert enrollments
                while ($row = $result->fetch_assoc()) {
                    $course_id = $row['course_id'];
                    $insert_enroll = $conn->prepare("INSERT INTO enrollments (student_id, course_id, semester) VALUES (?, ?, ?)");
                    $insert_enroll->bind_param("iii", $student_id, $course_id, $semester);
                    $insert_enroll->execute();
                }

                $success = "Student added successfully with courses auto-assigned.";
            } else {
                $error = "Error inserting into students table.";
            }
        } else {
            $error = "Error inserting into users table.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link rel="stylesheet" href="admin.css"> <!-- Optional: Your CSS -->
</head>
<body>
<?php include("./includes/header.php"); ?>

<div class="container">
    <h2>Add Student</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone" required><br><br>

        <label>Username:</label><br>
        <input type="text" name="username" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <label>Program:</label><br>
        <select name="program_id" required>
            <option value="">-- Select Program --</option>
            <?php while ($row = $programs_result->fetch_assoc()): ?>
                <option value="<?php echo $row['program_id']; ?>"><?php echo $row['program_name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Semester:</label><br>
        <select name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select><br><br>

        <button type="submit">Add Student</button>
    </form>
</div>

<?php include("./includes/footer.php"); ?>
</body>
</html>
