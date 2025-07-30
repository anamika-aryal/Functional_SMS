<?php
include("../includes/auth.php");
include("../includes/db.php");

// Ensure admin access

// Fetch counts for quick overview
$totalPrograms = $conn->query("SELECT COUNT(*) AS total FROM programs")->fetch_assoc()['total'] ?? 0;
$totalCourses  = $conn->query("SELECT COUNT(*) AS total FROM courses")->fetch_assoc()['total'] ?? 0;
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$totalTeachers = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'] ?? 0;
$totalEvents   = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include("includes/header.php"); ?>

<!-- Navigation / Quick Actions -->
<div class="navigation">
    <h2>Admin Dashboard</h2>
    <div class="actions">
        <a href="add-program.php" class="btn btn-primary">➕ Add Program</a>
        <a href="add-course.php" class="btn btn-primary">➕ Add Course</a>
        <a href="assign-courses-to-program.php" class="btn btn-primary">📑 Assign Courses</a>
        <a href="add-student.php" class="btn btn-primary">➕ Add Student</a>
        <a href="../logout.php" class="btn btn-primary">Logout</a>
    </div>
</div>

<!-- Quick Overview -->
<div class="card">
    <h3>Quick Overview</h3>
    <table>
        <tr>
            <th>Total Programs</th>
            <th>Total Courses</th>
            <th>Total Students</th>
            <th>Total Teachers</th>
            <th>Upcoming Events</th>
        </tr>
        <tr>
            <td><?= $totalPrograms ?></td>
            <td><?= $totalCourses ?></td>
            <td><?= $totalStudents ?></td>
            <td><?= $totalTeachers ?></td>
            <td><?= $totalEvents ?></td>
        </tr>
    </table>
</div>

<!-- Students Table -->
<div class="card">
    <h3>Students
        <a href="add-student.php" class="btn btn-primary" style="float:right;">➕ Add Student</a>
    </h3>
    <table>
        <tr>
            <th>Name</th><th>Email</th><th>Phone</th><th>Program</th><th>Semester</th><th>Actions</th>
        </tr>
        <?php
        $res = $conn->query("SELECT s.student_id, s.first_name, s.last_name, s.phone, s.program_id, s.semester, u.email, p.program_name
                             FROM students s
                             JOIN users u ON s.user_id = u.user_id
                             LEFT JOIN programs p ON s.program_id = p.program_id
                             ORDER BY s.student_id DESC");
        while ($row = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['first_name']} {$row['last_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['phone']}</td>
                    <td>" . ($row['program_name'] ?? '-') . "</td>
                    <td>" . ($row['semester'] ?? '-') . "</td>
                    <td>
                        <a href='update-student.php?student_id={$row['student_id']}'>✏ Edit</a> |
                        <a href='delete-student.php?student_id={$row['student_id']}' onclick='return confirm(\"Delete this student?\")'>🗑 Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</div>

<!-- Teachers Table -->
<div class="card">
    <h3>Teachers
        <a href="add-teacher.php" class="btn btn-primary" style="float:right;">➕ Add Teacher</a>
    </h3>
    <table>
        <tr>
            <th>Name</th><th>Email</th><th>Phone</th><th>Actions</th>
        </tr>
        <?php
        $res = $conn->query("SELECT t.teacher_id, t.first_name, t.last_name, t.phone, u.email
                             FROM teachers t
                             JOIN users u ON t.user_id = u.user_id
                             ORDER BY t.teacher_id DESC");
        while ($row = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['first_name']} {$row['last_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['phone']}</td>
                    <td>
                        <a href='update-teacher.php?teacher_id={$row['teacher_id']}'>✏ Edit</a> |
                        <a href='delete-teacher.php?teacher_id={$row['teacher_id']}' onclick='return confirm(\"Delete this teacher?\")'>🗑 Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</div>

<!-- Courses Table -->
<div class="card">
    <h3>Courses
        <a href="add-course.php" class="btn btn-primary" style="float:right;">➕ Add Course</a>
    </h3>
    <table>
        <tr>
            <th>Course Code</th><th>Course Name</th><th>Credits</th><th>Actions</th>
        </tr>
        <?php
        $res = $conn->query("SELECT * FROM courses ORDER BY course_id DESC");
        while ($row = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['course_code']}</td>
                    <td>{$row['course_name']}</td>
                    <td>{$row['credits']}</td>
                    <td>
                        <a href='update-course.php?course_id={$row['course_id']}'>✏ Edit</a> |
                        <a href='delete-course.php?course_id={$row['course_id']}' onclick='return confirm(\"Delete this course?\")'>🗑 Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</div>

<!-- Event Cards -->
<div class="card">
    <h3>Recent Events
        <a href="add-event.php" class="btn btn-primary" style="float:right;">➕ Add Event</a>
    </h3>
    <div class="event-cards">
        <?php
        $eventQuery = $conn->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 5");
        while ($row = $eventQuery->fetch_assoc()) {
            echo "<div class='event-card'>
                    <h4>{$row['title']}</h4>
                    <p>Date: {$row['event_date']}</p>
                    <p>Venue: {$row['venue']}</p>
                    <a href='delete-event.php?event_id={$row['event_id']}' class='event-delete' onclick='return confirm(\"Delete this event?\")'>Delete</a>
                  </div>";
        }
        ?>
    </div>
</div>


<?php include("includes/footer.php"); ?>
</body>
</html>
