
<?php
include("../includes/auth.php");
include("../includes/db.php");

$event_id = $_GET['event_id'] ?? 0;

if ($event_id > 0) {
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
}

header("Location: dashboard.php?msg=Event Deleted");
exit;
?>
