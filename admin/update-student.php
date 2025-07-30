<?php
include("./includes/auth.php");
include("./includes/db.php");

$success = $error = "";

// Get student_id from query
if (!isset($_GET['student_id'])) {
    die("Student ID not provided.");
}
$student_id = intval($_GET['student_id']);

// Fetch all programs
$programs_result = $conn->query("SELECT * FROM programs");

// Fetch student data
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

// On submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $username = trim($_POST["username"]);

    $program_id = intval($_POST["program_id"]);
    $semester = intval($_POST["semester"]);

    if (!$first_name || !$last_name || !$username || !$email || !$program_id || !$semester) {
        $error = "All fields are required.";
    } else {
        // Update users table
        $stmt1 = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        $stmt1->bind_param("ssi", $username, $email, $student['user_id']);
        $stmt1->execute();

        // Update students table
        $stmt2 = $conn->prepare("UPDATE students SET first_name = ?, last_name = ?, phone = ?, program_id = ?, semester = ? WHERE student_id = ?");
        $stmt2->bind_param("sssiii", $first_name, $last_name, $phone, $program_id, $semester, $student_id);
        if ($stmt2->execute()) {
            // Remove existing enrollments
            $conn->query("DELETE FROM enrollments WHERE student_id = $student_id");

            // Fetch new courses
            $course_q = $conn->prepare("SELECT course_id FROM program_courses WHERE program_id = ? AND semester = ?");
            $course_q->bind_param("ii", $program_id, $semester);
            $course_q->execute();
            $result = $course_q->get_result();

            // Re-insert enrollments
            while ($row = $result->fetch_assoc()) {
                $course_id = $row['course_id'];
                $insert_enroll = $conn->prepare("INSERT INTO enrollments (student_id, course_id, semester) VALUES (?, ?, ?)");
                $insert_enroll->bind_param("iii", $student_id, $course_id, $semester);
                $insert_enroll->execute();
            }

            $success = "Student updated successfully with new course mappings.";
        } else {
            $error = "Failed to update student.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Student</title>
</head>
<body>
<?php include("header.php"); ?>

<div class="container">
    <h2>Update Student</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>First Name:</label><br>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required><br><br>

        <label>Phone:</label><br>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required><br><br>

        <label>Username:</label><br>
        <input type="text" name="username" value="<?php echo htmlspecialchars($student['username']); ?>" required><br><br>

        <label>Program:</label><br>
        <select name="program_id" required>
            <option value="">-- Select Program --</option>
            <?php
            $programs_result->data_seek(0); // rewind
            while ($row = $programs_result->fetch_assoc()):
                $selected = $row['program_id'] == $student['program_id'] ? "selected" : "";
                echo "<option value='{$row['program_id']}' $selected>{$row['program_name']}</option>";
            endwhile;
            ?>
        </select><br><br>

        <label>Semester:</label><br>
        <select name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?php echo $i; ?>" <?php if ($i == $student['semester']) echo 'selected'; ?>><?php echo $i; ?></option>
            <?php endfor; ?>
        </select><br><br>

        <button type="submit">Update Student</button>
    </form>
</div>

<?php include("footer.php"); ?>
</body>
</html>
