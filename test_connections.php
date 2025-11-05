<?php
$DB_HOST = 'sql100.infinityfree.com';
$DB_USER = 'if0_40337753';
$DB_PASS = 'lr3fB6jGpV';
$DB_NAME = 'if0_40337753_matrixdb';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
} 
echo "✅ Database connected successfully!";
?>
