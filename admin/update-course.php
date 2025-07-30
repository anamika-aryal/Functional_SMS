<?php
include("../includes/auth.php");
include("../includes/db.php");

$success = $error = "";

// Get course ID
if (!isset($_GET['id'])) {
    die("Course ID not provided.");
}
$course_id = intval($_GET['id']);

// Fetch course info
$course_stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
$course_stmt->bind_param("i", $course_id);
$course_stmt->execute();
$course_result = $course_stmt->get_result();
$course = $course_result->fetch_assoc();

if (!$course) {
    die("Course not found.");
}

// Fetch program-course mapping
$map_stmt = $conn->prepare("SELECT * FROM program_courses WHERE course_id = ? LIMIT 1");
$map_stmt->bind_param("i", $course_id);
$map_stmt->execute();
$map_result = $map_stmt->get_result();
$mapping = $map_result->fetch_assoc();

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
        // Update course info
        $update_stmt = $conn->prepare("UPDATE courses SET course_code = ?, course_name = ?, credits = ?, instructor_id = ? WHERE course_id = ?");
        $update_stmt->bind_param("ssiii", $course_code, $course_name, $credits, $instructor_id, $course_id);
        $update_stmt->execute();

        // Update program_courses mapping
        if ($mapping) {
            $map_update_stmt = $conn->prepare("UPDATE program_courses SET program_id = ?, semester = ? WHERE course_id = ?");
            $map_update_stmt->bind_param("iii", $program_id, $semester, $course_id);
            $map_update_stmt->execute();
        } else {
            // If not mapped yet, insert new mapping
            $map_insert_stmt = $conn->prepare("INSERT INTO program_courses (program_id, course_id, semester) VALUES (?, ?, ?)");
            $map_insert_stmt->bind_param("iii", $program_id, $course_id, $semester);
            $map_insert_stmt->execute();
        }

        $success = "Course updated successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Course</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include("header.php"); ?>

<div class="container">
    <h2>Update Course</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Course Code:</label><br>
        <input type="text" name="course_code" value="<?php echo htmlspecialchars($course['course_code']); ?>" required><br><br>

        <label>Course Name:</label><br>
        <input type="text" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required><br><br>

        <label>Credits:</label><br>
        <input type="number" name="credits" min="1" value="<?php echo $course['credits']; ?>" required><br><br>

        <label>Instructor ID:</label><br>
        <input type="number" name="instructor_id" value="<?php echo $course['instructor_id']; ?>" required><br><br>

        <label>Program:</label><br>
        <select name="program_id" required>
            <option value="">-- Select Program --</option>
            <?php while ($p = $programs_result->fetch_assoc()): ?>
                <option value="<?php echo $p['program_id']; ?>" <?php if ($mapping && $p['program_id'] == $mapping['program_id']) echo 'selected'; ?>>
                    <?php echo $p['program_name']; ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Semester:</label><br>
        <select name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?php echo $i; ?>" <?php if ($mapping && $i == $mapping['semester']) echo 'selected'; ?>>
                    <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select><br><br>

        <button type="submit">Update Course</button>
    </form>
</div>

<?php include("footer.php"); ?>
</body>
</html>
