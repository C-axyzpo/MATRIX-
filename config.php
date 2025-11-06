<?php
// config.php — connected to FreeDB.tech cloud MySQL

$DB_HOST = 'sql.freedb.tech';
$DB_USER = 'freedb_thematrix';
$DB_PASS = '*MVy9zvP6BxX2MW';  // remove the * at start
$DB_NAME = 'freedb_matrixproject';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
