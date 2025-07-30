<?php
session_start();
require_once("../includes/db.php");

// Role-based access for Teacher (role_id = 2)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get teacher info
$teacher = $conn->query("SELECT teacher_id, first_name, last_name FROM teachers WHERE user_id = $user_id")->fetch_assoc();
$teacher_id = $teacher['teacher_id'];

// Fetch courses assigned to this teacher
$courses = $conn->query("
    SELECT course_id, course_name, course_code
    FROM courses
    WHERE instructor_id = $teacher_id
");

// Count assignments
$assignments_count = $conn->query("
    SELECT COUNT(*) AS total 
    FROM assignments 
    WHERE course_id IN (SELECT course_id FROM courses WHERE instructor_id = $teacher_id)
")->fetch_assoc()['total'];

// Count students under this teacher
$students_count = $conn->query("
    SELECT COUNT(DISTINCT e.student_id) AS total
    FROM enrollments e
    JOIN courses c ON e.course_id = c.course_id
    WHERE c.instructor_id = $teacher_id
")->fetch_assoc()['total'];

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
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="teacher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="navigation">
        <h2>Welcome, <?= htmlspecialchars($teacher['first_name']) ?> <?= htmlspecialchars($teacher['last_name']) ?></h2>
        <div>
            <a href="dashboard.php" class="btn btn-primary">Dashboard</a>
            <a href="assignments.php">Assignments</a>
            <a href="attendance.php">Attendance</a>
            <a href="add_result.php">Results</a>
            <a href="profile_setup.php">Profile</a>
            <a href="../logout.php">Logout</a>
        </div>
    </div>

  <!-- Quick Overview Widgets -->

<div class="quick-overview">
    <div class="overview-widget">
        <div class="overview-icon">📚</div>
        <div class="overview-details">
            <h4>Courses Assigned</h4>
            <p><?= $courses->num_rows ?></p>
        </div>
    </div>

    <div class="overview-widget">
        <div class="overview-icon">👨‍🎓</div>
        <div class="overview-details">
            <h4>Students Enrolled</h4>
            <p><?= $students_count ?></p>
        </div>
    </div>

    <div class="overview-widget">
        <div class="overview-icon">📝</div>
        <div class="overview-details">
            <h4>Total Assignments</h4>
            <p><?= $assignments_count ?></p>
        </div>
    </div>
</div>


    <!-- Assigned Courses -->
    <div class="card">
        <h3>📚 My Courses</h3>
        <table>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Actions</th>
            </tr>
            <?php if ($courses->num_rows > 0): ?>
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($course['course_code']) ?></td>
                        <td><?= htmlspecialchars($course['course_name']) ?></td>
                        <td>
                            <a href="assignments.php?course_id=<?= $course['course_id'] ?>">Assignments</a> | 
                            <a href="attendance.php?course_id=<?= $course['course_id'] ?>">Attendance</a> | 
                            <a href="add_result.php?course_id=<?= $course['course_id'] ?>">Results</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">No courses assigned.</td></tr>
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
                        <p>Date: <?= htmlspecialchars($row['event_date']) ?></p>
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
