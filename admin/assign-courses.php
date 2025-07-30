<?php
include("./includes/auth.php");
include("./includes/db.php");

$success = $error = "";

// Fetch all programs for dropdown
$programs_result = $conn->query("SELECT * FROM programs");

// Fetch all instructors for dropdown
$instructor_result = $conn->query("
    SELECT t.teacher_id, t.first_name, t.last_name 
    FROM teachers t
    JOIN users u ON t.user_id = u.user_id
");

$instructors = [];
while ($row = $instructor_result->fetch_assoc()) {
    $instructors[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $program_id = intval($_POST["program_id"]);
    $semester = intval($_POST["semester"]);

    $course_codes = $_POST['course_code'];
    $course_names = $_POST['course_name'];
    $credits_list = $_POST['credits'];
    $instructor_ids = $_POST['instructor_id'];

    if (!$program_id || !$semester) {
        $error = "Please select a valid program and semester.";
    } else {
        for ($i = 0; $i < count($course_codes); $i++) {
            $code = trim($course_codes[$i]);
            $name = trim($course_names[$i]);
            $credits = intval($credits_list[$i]);
            $instructor = intval($instructor_ids[$i]);

            if (empty($code) || empty($name) || $credits <= 0 || $instructor <= 0) continue;

            // Check if course already exists
            $stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $course_id = $row['course_id'];
            } else {
                // Insert into courses
                $insert = $conn->prepare("INSERT INTO courses (course_code, course_name, credits, instructor_id) VALUES (?, ?, ?, ?)");
                $insert->bind_param("ssii", $code, $name, $credits, $instructor);
                $insert->execute();
                $course_id = $insert->insert_id;
            }

            // Insert mapping into program_courses
            $map_check = $conn->prepare("SELECT * FROM program_courses WHERE program_id = ? AND course_id = ? AND semester = ?");
            $map_check->bind_param("iii", $program_id, $course_id, $semester);
            $map_check->execute();
            $map_result = $map_check->get_result();

            if ($map_result->num_rows === 0) {
                $map = $conn->prepare("INSERT INTO program_courses (program_id, course_id, semester) VALUES (?, ?, ?)");
                $map->bind_param("iii", $program_id, $course_id, $semester);
                $map->execute();
            }
        }

        $success = "Courses assigned to semester successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Courses</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include("./includes/header.php"); ?>

<div class="container">
    <h2>Assign Courses to Program & Semester</h2>

    <?php if ($success): ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Program:</label>
        <select name="program_id" required>
            <option value="">-- Select Program --</option>
            <?php while ($p = $programs_result->fetch_assoc()): ?>
                <option value="<?php echo $p['program_id']; ?>"><?php echo $p['program_name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Semester:</label>
        <select name="semester" required>
            <option value="">-- Select Semester --</option>
            <?php for ($i = 1; $i <= 8; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select><br><br>

        <h3>Courses to Add</h3>
        <div id="course-group">
            <div class="course-entry">
                <input type="text" name="course_code[]" placeholder="Course Code" required>
                <input type="text" name="course_name[]" placeholder="Course Name" required>
                <input type="number" name="credits[]" placeholder="Credits" required>
                <select name="instructor_id[]" required>
                    <option value="">-- Select Instructor --</option>
                    <?php foreach ($instructors as $ins): ?>
                        <option value="<?= $ins['teacher_id']; ?>">
                            <?= $ins['first_name'] . ' ' . $ins['last_name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <br>
        <button type="button" onclick="addCourse()">➕ Add More</button><br><br>
        <button type="submit">Assign Courses</button>
    </form>
</div>

<script>
const instructors = <?= json_encode($instructors); ?>;

function addCourse() {
    const group = document.getElementById("course-group");
    const div = document.createElement("div");
    div.className = "course-entry";

    let selectHTML = `<select name="instructor_id[]" required><option value="">-- Select Instructor --</option>`;
    instructors.forEach(ins => {
        selectHTML += `<option value="${ins.teacher_id}">${ins.first_name} ${ins.last_name}</option>`;
    });
    selectHTML += `</select>`;

    div.innerHTML = `
        <input type="text" name="course_code[]" placeholder="Course Code" required>
        <input type="text" name="course_name[]" placeholder="Course Name" required>
        <input type="number" name="credits[]" placeholder="Credits" required>
        ${selectHTML}
    `;
    group.appendChild(div);
}
</script>

<?php include("./includes/footer.php"); ?>
</body>
</html>
