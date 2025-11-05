<?php
// config.php — change DB credentials if needed
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'attendance';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
