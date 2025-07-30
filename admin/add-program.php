<?php
include("../includes/auth.php");
include("../includes/db.php");

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $program_name = trim($_POST["program_name"]);
    $duration_years = intval($_POST["duration_years"]);
    $description = trim($_POST["description"]);

    // Validation
    if (empty($program_name) || $duration_years < 1) {
        $error = "Program name and valid duration are required.";
    } else {
        // Check if program already exists
        $stmt = $conn->prepare("SELECT * FROM programs WHERE program_name = ?");
        $stmt->bind_param("s", $program_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Program already exists.";
        } else {
            // Insert new program
            $stmt = $conn->prepare("INSERT INTO programs (program_name, duration_years, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $program_name, $duration_years, $description);
            if ($stmt->execute()) {
                $success = "Program added successfully.";
            } else {
                $error = "Failed to add program.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Program</title>
    <link rel="stylesheet" href="admin.css"> <!-- Optional: your CSS file -->
</head>
<body>
<?php include("./includes/header.php"); ?>

    <div class="container">
        <h2>Add New Program</h2>

        <?php if ($success): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php elseif ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Program Name:</label><br>
            <input type="text" name="program_name" required><br><br>

            <label>Duration (in years):</label><br>
            <input type="number" name="duration_years" min="1" required><br><br>

            <label>Description (optional):</label><br>
            <textarea name="description" rows="3" cols="30"></textarea><br><br>

            <button type="submit">Add Program</button>
        </form>
    </div>

<?php include("./includes/footer.php"); ?>
</body>
</html>
