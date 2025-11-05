<?php
// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "attendance");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$uid = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uid = $_POST['uid'];
} elseif ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['uid'])) {
    $uid = $_GET['uid'];
}

if (!empty($uid)) {
    $timestamp = date("Y-m-d H:i:s");

    // Check if UID exists in users table
    $sql = "SELECT * FROM users WHERE uid='$uid'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
    $conn->query("INSERT INTO logs (uid, name, timestamp) VALUES ('$uid', '$name', '$timestamp')");
    echo "OK";
} else {
    echo "UNKNOWN";
}

}

$conn->close();
?>
