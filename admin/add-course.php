
<?php
include("../includes/auth.php");
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_code = $_POST['course_code'];
    $course_name = $_POST['course_name'];
    $credits = $_POST['credits'];
    $instructor_id = $_POST['instructor_id'];

    $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, credits, instructor_id) VALUES (?,?,?,?)");
    $stmt->bind_param("ssii", $course_code, $course_name, $credits, $instructor_id);
    $stmt->execute();

    header("Location: dashboard.php?msg=Course Added");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Course</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="navigation">
        <h2>Add Course</h2>
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <div class="form-container">
        <form method="POST">
            <label>Course Code</label>
            <input type="text" name="course_code" required>

            <label>Course Name</label>
            <input type="text" name="course_name" required>

            <label>Credits</label>
            <input type="number" name="credits" required min="1" max="10">

            <label>Instructor</label>
            <select name="instructor_id" required>
                <option value="">Select Teacher</option>
                <?php
                $teachers = $conn->query("SELECT teacher_id, first_name, last_name FROM teachers");
                while ($t = $teachers->fetch_assoc()) {
                    echo "<option value='{$t['teacher_id']}'>{$t['first_name']} {$t['last_name']}</option>";
                }
                ?>
            </select>

            <button class="btn btn-primary" type="submit">Add Course</button>
        </form>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
