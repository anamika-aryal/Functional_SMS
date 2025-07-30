
<?php
include("../includes/auth.php");
include("../includes/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $event_date = $_POST['event_date'];
    $venue = $_POST['venue'];

    $stmt = $conn->prepare("INSERT INTO events (title, event_date, venue) VALUES (?,?,?)");
    $stmt->bind_param("sss", $title, $event_date, $venue);
    $stmt->execute();

    header("Location: dashboard.php?msg=Event Added");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Event</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="navigation">
        <h2>Add Event</h2>
        <a href="dashboard.php">⬅ Back to Dashboard</a>
    </div>

    <div class="form-container">
        <form method="POST">
            <label>Event Title</label>
            <input type="text" name="title" required>

            <label>Event Date</label>
            <input type="datetime-local" name="event_date" required>

            <label>Venue</label>
            <input type="text" name="venue" required>

            <button class="btn btn-primary" type="submit">Add Event</button>
        </form>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>
