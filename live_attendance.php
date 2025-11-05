<?php
// ===================
// Database Connection
// ===================
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance";

$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ===================
// Get Latest Scan
// ===================
$sql = "SELECT uid, name, grade, section, timestamp, status 
        FROM logs
        ORDER BY timestamp DESC 
        LIMIT 1";

$result = $conn->query($sql);
$latest = null;
if ($result && $result->num_rows > 0) {
    $latest = $result->fetch_assoc();
}

// ===================
// Auto Refresh Every 2 Sec
// ===================
header("Refresh:2"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #111;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .card {
            background: #1a1a1a;
            padding: 60px;
            border-radius: 25px;
            text-align: center;
            width: 700px;
            max-width: 90%;
            box-shadow: 0 0 40px rgba(0, 255, 255, 0.5);
            animation: fadeIn 0.5s ease-in-out;
        }
        .card div {
            margin: 20px 0;
            font-size: 42px;
            font-weight: bold;
        }
        .placeholder {
            font-size: 36px;
            color: #777;
        }

        /* Fade-in animation for new scan */
        @keyframes fadeIn {
            0% {opacity: 0; transform: translateY(-20px);}
            100% {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
    <?php if ($latest): ?>
        <div class="card">
            <div>Name: <?php echo htmlspecialchars($latest['name']); ?></div>
            <div>Section: <?php echo htmlspecialchars($latest['section']); ?></div>
            <div>UID: <?php echo htmlspecialchars($latest['uid']); ?></div>
            <div>Time: <?php echo htmlspecialchars($latest['timestamp']); ?></div>
        </div>
    <?php else: ?>
        <div class="placeholder">No scans yet. Please tap a card.</div>
    <?php endif; ?>
</body>
</html>
