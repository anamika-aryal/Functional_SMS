<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Student (role_id = 3)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 3) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student info
$student = $conn->query("SELECT student_id, first_name, last_name FROM students WHERE user_id = $user_id")->fetch_assoc();
$student_id = $student['student_id'];

/* -----------------------------
   LIVE METRICS CALCULATION
------------------------------*/

// Attendance %
$att_sql = "
    SELECT 
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present_days,
        COUNT(*) AS total_days
    FROM attendance
    WHERE student_id = $student_id
";
$att_data = $conn->query($att_sql)->fetch_assoc();
$attendance_percentage = ($att_data['total_days'] > 0) 
    ? round(($att_data['present_days'] / $att_data['total_days']) * 100, 2) 
    : 0;

// Pending Assignments
$pending_sql = "
    SELECT COUNT(*) AS pending
    FROM assignments a
    JOIN enrollments e ON a.course_id = e.course_id
    WHERE e.student_id = $student_id
      AND a.assignment_id NOT IN (
          SELECT assignment_id FROM submissions WHERE student_id = $student_id
      )
      AND a.due_date >= CURDATE()
";
$pending = $conn->query($pending_sql)->fetch_assoc()['pending'] ?? 0;

// Current GPA (average grade_point from results)
$gpa_sql = "
    SELECT ROUND(AVG(grade_point), 2) AS gpa
    FROM results
    WHERE student_id = $student_id
";
$gpa = $conn->query($gpa_sql)->fetch_assoc()['gpa'] ?? 0;

// Total Credits from enrolled courses
$credits_sql = "
    SELECT SUM(c.credits) AS total_credits
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE e.student_id = $student_id
";
$credits = $conn->query($credits_sql)->fetch_assoc()['total_credits'] ?? 0;

/* -----------------------------
   RECENT ASSIGNMENTS
------------------------------*/
$assignments = $conn->query("
    SELECT a.title, a.due_date, c.course_name
    FROM assignments a
    JOIN courses c ON a.course_id = c.course_id
    JOIN enrollments e ON e.course_id = a.course_id
    WHERE e.student_id = $student_id
    ORDER BY a.due_date ASC
    LIMIT 5
");

/* -----------------------------
   UPCOMING EVENTS
------------------------------*/
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
        <a href="my_submissions.php">My Submissions</a> 
        <a href="attendance.php">Attendance</a>
        <a href="result.php">Results</a>
        <a href="profile_setup.php">Profile</a>
        <a href="../logout.php">Logout</a>
    </div>
</div>

    <!-- Quick Overview Widgets -->
<div class="quick-overview">
    <div class="overview-widget">
        <div class="overview-icon">📅</div>
        <div class="overview-details">
            <h4>Attendance</h4>
            <p><?= $attendance_percentage ?>%</p>
        </div>
    </div>

    <div class="overview-widget">
        <div class="overview-icon">🎓</div>
        <div class="overview-details">
            <h4>Current GPA</h4>
            <p><?= $gpa ?: 'N/A' ?></p>
        </div>
    </div>

    <div class="overview-widget">
        <div class="overview-icon">📚</div>
        <div class="overview-details">
            <h4>Total Credits</h4>
            <p><?= $credits ?: 0 ?></p>
        </div>
    </div>

    <div class="overview-widget">
        <div class="overview-icon">📝</div>
        <div class="overview-details">
            <h4>Pending Assignments</h4>
            <p><?= $pending ?></p>
        </div>
    </div>
</div>


    <!-- Recent Assignments -->
    <div class="card">
        <h3>📝 Recent Assignments</h3>
        <table>
            <tr>
                <th>Course</th><th>Title</th><th>Due Date</th>
            </tr>
            <?php if ($assignments->num_rows > 0): ?>
                <?php while ($row = $assignments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= date("d M Y", strtotime($row['due_date'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">No assignments yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <!-- Upcoming Events -->
    <div class="card">
        <h3>📅 Upcoming Events</h3>
        <div class="event-cards">
            <?php if ($events->num_rows > 0): ?>
                <?php while ($row = $events->fetch_assoc()): ?>
                    <div class="event-card">
                        <h4><?= htmlspecialchars($row['title']) ?></h4>
                        <p>Date: <?= date("d M Y", strtotime($row['event_date'])) ?></p>
                        <p>Venue: <?= htmlspecialchars($row['venue']) ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No upcoming events.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include("includes/footer.php"); ?>
</body>
</html>
