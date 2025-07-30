<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Teacher (role_id = 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get teacher_id
$teacher = $conn->query("SELECT teacher_id FROM teachers WHERE user_id = $user_id")->fetch_assoc();
$teacher_id = $teacher['teacher_id'];

// Selected course
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch teacher's courses
$courses = $conn->query("
    SELECT course_id, course_name, course_code
    FROM courses
    WHERE instructor_id = $teacher_id
");

// If course selected, fetch enrolled students
$students = [];
if ($course_id) {
    $students = $conn->query("
        SELECT st.student_id, st.first_name, st.last_name
        FROM enrollments e
        JOIN students st ON e.student_id = st.student_id
        WHERE e.course_id = $course_id
        ORDER BY st.first_name
    ");
}

$success = $error = "";

// Handle form submission for results
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['results'])) {
    foreach ($_POST['results'] as $student_id => $data) {
        $internal = intval($data['internal_marks']);
        $external = intval($data['external_marks']);
        $total = $internal + $external;
        $grade_point = $total >= 90 ? 10 :
                       ($total >= 80 ? 9 :
                       ($total >= 70 ? 8 :
                       ($total >= 60 ? 7 :
                       ($total >= 50 ? 6 :
                       ($total >= 40 ? 5 : 0)))));
        $letter_grade = $grade_point >= 10 ? 'A+' :
                        ($grade_point == 9 ? 'A' :
                        ($grade_point == 8 ? 'B+' :
                        ($grade_point == 7 ? 'B' :
                        ($grade_point == 6 ? 'C' :
                        ($grade_point == 5 ? 'D' : 'F')))));

        $stmt = $conn->prepare("
            INSERT INTO results (student_id, course_id, internal_marks, external_marks, total_marks, grade_point, letter_grade, exam_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Regular')
            ON DUPLICATE KEY UPDATE 
                internal_marks = VALUES(internal_marks),
                external_marks = VALUES(external_marks),
                total_marks = VALUES(total_marks),
                grade_point = VALUES(grade_point),
                letter_grade = VALUES(letter_grade)
        ");
        $stmt->bind_param("iiiiiss", $student_id, $course_id, $internal, $external, $total, $grade_point, $letter_grade);
        $stmt->execute();
    }

    $success = "Results saved successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enter Results</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>
    <div class="navigation">
        <h2>Enter Student Results</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="add_result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>🎓 Enter Results for Course</h3>

        <?php if ($success): ?>
            <p style="color:green;"><?= $success ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color:red;"><?= $error ?></p>
        <?php endif; ?>

        <!-- Course Selection -->
        <form method="GET" style="margin-bottom:20px;">
            <label for="course_id">Select Course:</label>
            <select name="course_id" required>
                <option value="">Select Course</option>
                <?php while ($c = $courses->fetch_assoc()): ?>
                    <option value="<?= $c['course_id'] ?>" <?= ($course_id == $c['course_id']) ? 'selected' : '' ?>>
                        <?= $c['course_code'] ?> - <?= $c['course_name'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary">Load Students</button>
        </form>

        <?php if ($course_id && $students && $students->num_rows > 0): ?>
            <form method="POST">
                <table>
                    <tr>
                        <th>Student Name</th>
                        <th>Internal Marks</th>
                        <th>External Marks</th>
                    </tr>
                    <?php while ($st = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $st['first_name'] . " " . $st['last_name'] ?></td>
                            <td><input type="number" name="results[<?= $st['student_id'] ?>][internal_marks]" min="0" max="50" required></td>
                            <td><input type="number" name="results[<?= $st['student_id'] ?>][external_marks]" min="0" max="50" required></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
                <button type="submit" class="btn btn-primary" style="margin-top:15px;">Save Results</button>
            </form>
        <?php elseif ($course_id): ?>
            <p>No students enrolled in this course.</p>
        <?php endif; ?>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
