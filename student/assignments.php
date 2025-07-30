<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Student (role_id = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id
$student = $conn->query("SELECT student_id, first_name FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['student_id'];

// Fetch assignments for enrolled courses
$query = "
    SELECT a.assignment_id, a.title, a.description, a.due_date, c.course_name,
           IFNULL(s.status, 'Not Submitted') AS status
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON e.course_id = c.course_id
    LEFT JOIN submissions s ON s.assignment_id = a.assignment_id AND s.student_id = $student_id
    WHERE e.student_id = $student_id
    ORDER BY a.due_date ASC
";
$assignments = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Assignments</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="navigation">
        <h2><?= $student['first_name']?>'s Assignments</h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <div class="card">
        <h3>📝 Assignment List</h3>
        <table>
            <tr>
                <th>Course</th>
                <th>Title</th>
                <th>Description</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php
            if ($assignments->num_rows > 0) {
                while ($row = $assignments->fetch_assoc()) {
                    $status = $row['status'];
                    $action = ($status == 'Not Submitted') 
                        ? "<a href='submit_assignment.php?assignment_id={$row['assignment_id']}'>Submit</a>" 
                        : "<span style='color:green;'>Submitted</span>";

                    echo "<tr>
                            <td>{$row['course_name']}</td>
                            <td>{$row['title']}</td>
                            <td>{$row['description']}</td>
                            <td>{$row['due_date']}</td>
                            <td>$status</td>
                            <td>$action</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No assignments found.</td></tr>";
            }
            ?>
        </table>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
