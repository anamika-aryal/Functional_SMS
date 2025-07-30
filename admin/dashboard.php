<?php
include("./includes/auth.php");
include("./includes/db.php");
include("./includes/header.php");

// Quick Overview Stats
$program_count = $conn->query("SELECT COUNT(*) AS total FROM programs")->fetch_assoc()['total'];
$course_count = $conn->query("SELECT COUNT(*) AS total FROM courses")->fetch_assoc()['total'];
$student_count = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$teacher_count = $conn->query("SELECT COUNT(*) AS total FROM teachers")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admin.css"> <!-- Your common CSS file -->
</head>
<body>

<div class="main">

    <!-- 🔥 Quick Overview Section -->
    <div class="overview-cards">
        <div class="event-card">
            <h4>📘 Programs</h4>
            <p>Total: <?php echo $program_count; ?></p>
        </div>
        <div class="event-card">
            <h4>📚 Courses</h4>
            <p>Total: <?php echo $course_count; ?></p>
        </div>
        <div class="event-card">
            <h4>👨‍🎓 Students</h4>
            <p>Total: <?php echo $student_count; ?></p>
        </div>
        <div class="event-card">
            <h4>👩‍🏫 Teachers</h4>
            <p>Total: <?php echo $teacher_count; ?></p>
        </div>
    </div>

    <!-- 🧑‍🎓 Students Table -->
    <div class="card">
        <h3>Students 
            <a href="add-student.php" class="btn btn-primary" style="float:right;">➕ Add Student</a>
        </h3>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Program</th>
                <th>Semester</th>
                <th>Actions</th>
            </tr>
            <?php
            $res = $conn->query("
                SELECT s.student_id, s.first_name, s.last_name, s.phone, u.email,
                       p.program_name, s.semester
                FROM students s
                JOIN users u ON s.user_id = u.user_id
                LEFT JOIN programs p ON s.program_id = p.program_id
                ORDER BY s.student_id DESC
            ");
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['first_name']} {$row['last_name']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['phone']}</td>
                        <td>" . ($row['program_name'] ?? '—') . "</td>
                        <td>" . ($row['semester'] ?? '—') . "</td>
                        <td>
                            <a href='view-student.php?student_id={$row['student_id']}'>👁 View</a> | 
                            <a href='update-student.php?student_id={$row['student_id']}'>✏ Edit</a> | 
                            <a href='delete-student.php?student_id={$row['student_id']}' onclick='return confirm(\"Delete this student?\")'>🗑 Delete</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </div>

    <!-- 👨‍🏫 Teachers Table -->
    <div class="card">
        <h3>Teachers 
            <a href="add-teacher.php" class="btn btn-primary" style="float:right;">➕ Add Teacher</a>
        </h3>
        <table>
            <tr>
                <th>Name</th><th>Email</th><th>Phone</th><th>Actions</th>
            </tr>
            <?php
            $res = $conn->query("
                SELECT t.teacher_id, t.first_name, t.last_name, t.phone, u.email
                FROM teachers t
                JOIN users u ON t.user_id = u.user_id
                ORDER BY t.teacher_id DESC
            ");
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

    <!-- 📘 Courses Table -->
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

    <!-- 📅 Event Cards -->
    <div class="card">
        <h3>Recent Events 
            <a href="add-event.php" class="btn btn-primary" style="float:right;">➕ Add Event</a>
        </h3>
        <div class="event-cards">
            <?php
            $eventQuery = $conn->query("SELECT * FROM events ORDER BY event_date DESC LIMIT 5");
            while ($row = $eventQuery->fetch_assoc()) {
                echo "
                    <div class='event-card'>
                        <h4>{$row['title']}</h4>
                        <p>Date: {$row['event_date']}</p>
                        <p>Venue: {$row['venue']}</p>
                        <a href='delete-event.php?event_id={$row['event_id']}' class='event-delete' onclick='return confirm(\"Delete this event?\")'>Delete</a>
                    </div>
                ";
            }
            ?>
        </div>
    </div>

</div>

<?php include("./includes/footer.php"); ?>
</body>
</html>
