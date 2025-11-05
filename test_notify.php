<?php
// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get UID from URL
if (isset($_GET['uid'])) {
    $rfid_uid = $_GET['uid'];

    // Check if UID exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE rfid_uid = ?");
    $stmt->bind_param("s", $rfid_uid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Log attendance
        $log_stmt = $conn->prepare("INSERT INTO attendance (user_id, rfid_uid) VALUES (?, ?)");
        $log_stmt->bind_param("is", $user_id, $rfid_uid);
        $log_stmt->execute();
        $log_stmt->close();

        // Send notification (example: onscreen)
        echo "<h3>Access Granted!</h3>";
        echo "User: " . htmlspecialchars($user['name']) . "<br>";
        echo "Logged at: " . date('Y-m-d H:i:s');
        
        // OPTIONAL: email notification
        // mail("your_email@example.com", "RFID Scan", "User ".$user['name']." scanned at ".date('Y-m-d H:i:s'));
    } else {
        echo "<h3>Access Denied!</h3>";
        echo "RFID UID not recognized.";
    }

    $stmt->close();
} else {
    echo "No UID provided. Use ?uid=YOUR_UID in URL.";
}

$conn->close();
?>
