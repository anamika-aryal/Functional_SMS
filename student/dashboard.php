<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Student (role_id = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch student quick metrics (if quick_view_metrics table exists)
$metrics = $conn->query("
    SELECT * FROM quick_view_metrics WHERE student_id = (
        SELECT student_id FROM students WHERE user_id = $user_id
    )
")->fetch_assoc();

$student = $conn->query("SELECT first_name, last_name FROM students WHERE user_id = $user_id")->fetch_assoc();

// Fetch recent assignments for student
$assignments = $conn->query("
    SELECT a.title, a.due_date, c.course_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON e.course_id = a.course_id
    JOIN students s ON s.student_id = e.student_id
    WHERE s.user_id = $user_id
    ORDER BY a.due_date ASC
    LIMIT 5
");

// Fetch upcoming events
$events = $conn->query("
    SELECT * FROM events
    WHERE event_date >= CURDATE()
    ORDER BY event_date ASC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student.css">
</head>
<body>
    <div class="navigation">
        <h2>Welcome, <?= $student['first_name'] ?> <?= $student['last_name']?> </h2>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

    <!-- Quick Overview -->
    <div class="card">
        <h3>📊 Quick Overview</h3>
        <p>Attendance: <strong><?= $metrics['attendance_percentage'] ?? 'N/A' ?>%</strong></p>
        <p>Current GPA: <strong><?= $metrics['current_gpa'] ?? 'N/A' ?></strong></p>
        <p>Total Credits: <strong><?= $metrics['total_credits'] ?? 'N/A' ?></strong></p>
        <p>Pending Assignments: <strong><?= $metrics['pending_assignments'] ?? '0' ?></strong></p>
    </div>

    <!-- Recent Assignments -->
    <div class="card">
        <h3>📝 Recent Assignments</h3>
        <table>
            <tr>
                <th>Course</th><th>Title</th><th>Due Date</th>
            </tr>
            <?php
            if ($assignments->num_rows > 0) {
                while ($row = $assignments->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['course_name']}</td>
                            <td>{$row['title']}</td>
                            <td>{$row['due_date']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No assignments yet.</td></tr>";
            }
            ?>
        </table>
    </div>

    <!-- Upcoming Events -->
    <div class="card">
        <h3>📅 Upcoming Events</h3>
        <div class="event-cards">
            <?php
            if ($events->num_rows > 0) {
                while ($row = $events->fetch_assoc()) {
                    echo "
                        <div class='event-card'>
                            <h4>{$row['title']}</h4>
                            <p>Date: {$row['event_date']}</p>
                            <p>Venue: {$row['venue']}</p>
                        </div>
                    ";
                }
            } else {
                echo "<p>No upcoming events.</p>";
            }
            ?>
        </div>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
