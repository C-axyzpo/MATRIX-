<?php
// Connect DB
$conn = new mysqli("localhost", "root", "", "attendance");
if ($conn->connect_error) die("DB failed: " . $conn->connect_error);

// Fetch latest clinic logs
$result = $conn->query("
    SELECT * 
    FROM clinic_logs
    ORDER BY timestamp DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Clinic Dashboard</title>
    <meta http-equiv="refresh" content="2"> <!-- Auto-refresh every 2 seconds -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 20px;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #333;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
        }
        button:hover {
            background-color: #0056b3;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #333;
            color: white;
        }
        tr:nth-child(even) { background: #f9f9f9; }
        tr:hover { background: #e8f0ff; }
    </style>
</head>
<body>
    <header>
        <h2>🏥 Clinic Entry Logs</h2>
        <button onclick="window.history.back()">⬅ Back</button>
    </header>

    <table>
        <tr>
            <th>ID</th>
            <th>UID</th>
            <th>Name</th>
            <th>Section</th>
            <th>Gender</th>
            <th>Grade</th>
            <th>Reason</th>
            <th>Timestamp</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['uid']) ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['section']) ?></td>
                <td><?= htmlspecialchars($row['gender'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['grade'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['reason'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($row['timestamp']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
