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

// Optional filter by course
$course_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Fetch assignments for teacher's courses
$query = "
    SELECT a.assignment_id, a.title, a.description, a.due_date, c.course_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    WHERE c.instructor_id = $teacher_id
";
if ($course_filter) {
    $query .= " AND a.course_id = $course_filter";
}
$query .= " ORDER BY a.due_date ASC";

$assignments = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assignments</title>
    <link rel="stylesheet" href="teacher.css">
</head>
<body>
    <div class="navigation">
        <h2>Assignments Management</h2>
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
        <h3>📚 Assignments 
            <a href="add_assignment.php" class="btn btn-primary" style="float:right;">➕ Add Assignment</a>
        </h3>
        <table>
            <tr>
                <th>Course</th>
                <th>Title</th>
                <th>Description</th>
                <th>Due Date</th>
                <th>Actions</th>
            </tr>
            <?php
            if ($assignments->num_rows > 0) {
                while ($row = $assignments->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['course_name']}</td>
                            <td>{$row['title']}</td>
                            <td>{$row['description']}</td>
                            <td>{$row['due_date']}</td>
                            <td>
                                <a href='view_submissions.php?assignment_id={$row['assignment_id']}'>View Submissions</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No assignments created yet.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
