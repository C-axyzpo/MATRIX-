<?php
// Connect to DB
$conn = new mysqli("localhost", "root", "", "attendance");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use JOIN so logs always show the latest user name
$sql = "SELECT logs.id, users.name, logs.uid, logs.timestamp
        FROM logs
        JOIN users ON logs.uid = users.uid
        ORDER BY logs.timestamp DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Logs</title>
</head>
<body>
    <h2>Attendance Logs</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>UID</th>
            <th>Timestamp</th>
        </tr>
        <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['uid']; ?></td>
            <td><?php echo $row['timestamp']; ?></td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>

<?php $conn->close(); ?>
