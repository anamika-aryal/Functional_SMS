<?php
include("../includes/auth.php");
include("../includes/db.php");

$success = $error = "";

// Fetch all programs for dropdown
$programs_result = $conn->query("SELECT * FROM programs");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = trim($_POST["course_code"]);
    $course_name = trim($_POST["course_name"]);
    $credits = intval($_POST["credits"]);
    $instructor_id = intval($_POST["instructor_id"]);
    $program_id = intval($_POST["program_id"]);
    $semester = intval($_POST["semester"]);

    if (empty($course_code) || empty($course_name) || $credits <= 0 || $semester <= 0 || !$program_id) {
        $error = "All fields are required.";
    } else {
        // Check if the course already exists
        $check_stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_code = ?");
        $check_stmt->bind_param("s", $course_code);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            $course_id = $row['course_id'];
        } else {
            // Insert into courses
            $insert_stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, credits, instructor_id) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssii", $course_code, $course_name, $credits, $instructor_id);
            if ($insert_stmt->execute()) {
                $course_id = $insert_stmt->insert_id;
            } else {
                $error = "Failed to insert course.";
            }
        }

        if (!isset($error)) {
            // Insert into program_courses
            $map_stmt = $conn->prepare("INSERT INTO program_courses (program_id, course_id, semester) VALUES (?, ?, ?)");
            $map_stmt->bind_param("iii", $program_id, $course_id, $semester);
            if ($map_stmt->execute()) {
                $success = "Course added successfully to program and semester.";
            } else {
                $error = "Failed to map course to program/semester (might already be mapped).";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Course</title>
    <link rel="stylesheet" href="admin.css"> <!-- Optional: Your CSS -->
</head>
<body>
<?php include("./includes/header.php"); ?>

<div class="container">
    <h2>Add Course to Program</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Course Code:</label><br>
        <input type="text" name="course_code" required><br><br>

        <label>Course Name:</label><br>
        <input type="text" name="course_name" required><br><br>

        <label>Credits:</label><br>
        <input type="number" name="credits" min="1" required><br><br>

        <label>Instructor ID:</label><br>
        <input type="number" name="instructor_id" required><br><br>

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

        <button type="submit">Add Course</button>
    </form>
</div>

<?php include("./includes/footer.php"); ?>
</body>
</html>
