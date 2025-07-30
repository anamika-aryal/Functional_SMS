
<?php
include("../includes/auth.php");
include("../includes/db.php");

$course_id = $_GET['course_id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

if (!$course) {
    die("Course not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    $instructor_id = $_POST['instructor_id'];

    $stmt = $conn->prepare("UPDATE courses SET course_code=?, course_name=?, credits=?, instructor_id=? WHERE course_id=?");
    $stmt->bind_param("ssiii", $course_code, $course_name, $credits, $instructor_id, $course_id);
    $stmt->execute();

    header("Location: dashboard.php?msg=Course Updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Course</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="navigation">
        <h2>Update Course</h2>
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <div class="form-container">
        <form method="POST">
            <label>Course Code</label>
            <input type="text" name="course_code" value="<?= $course['course_code']; ?>" required>

            <label>Course Name</label>
            <input type="text" name="course_name" value="<?= $course['course_name']; ?>" required>

            <label>Credits</label>
            <input type="number" name="credits" value="<?= $course['credits']; ?>" required min="1" max="10">

            <label>Instructor</label>
            <select name="instructor_id" required>
                <option value="">Select Teacher</option>
                <?php
                $teachers = $conn->query("SELECT teacher_id, first_name, last_name FROM teachers");
                while ($t = $teachers->fetch_assoc()) {
                    $selected = ($course['instructor_id'] == $t['teacher_id']) ? "selected" : "";
                    echo "<option value='{$t['teacher_id']}' $selected>{$t['first_name']} {$t['last_name']}</option>";
                }
                ?>
            </select>

            <button type="submit" class="btn btn-primary">Update Course</button>
        </form>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
