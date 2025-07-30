<?php
$host = "sql305.infinityfree.com";
$db = "if0_39598003_sms";
$user = "if0_39598003";
$pass = "oicpmI61EH";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>