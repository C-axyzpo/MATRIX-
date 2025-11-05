<?php
// Connect to DB
$conn = new mysqli("localhost", "root", "", "attendance");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search
$search = $_GET['search'] ?? '';
$filter = $conn->real_escape_string($search);

$sql = "SELECT logs.id, users.name, users.role, logs.uid, logs.timestamp
        FROM logs
        LEFT JOIN users ON logs.uid = users.uid
        WHERE users.name LIKE '%$filter%' 
           OR users.role LIKE '%$filter%' 
           OR logs.uid LIKE '%$filter%'
        ORDER BY logs.id DESC";

$result = $conn->query($sql);
?>
<html>
<head>
    <title>Attendance Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        h2 { margin-bottom: 20px; text-align: center; color: #333; }
        .search-box { text-align: center; margin-bottom: 20px; }
        input[type="text"] { padding: 8px; width: 250px; }
        input[type="submit"] { padding: 8px 15px; cursor: pointer; background: #007bff; border: none; color: white; }
        input[type="submit"]:hover { background: #0056b3; }
        table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        tr:hover { background-color: #e6f2ff; }
    </style>
</head>
<body>
  <h2>📋 Attendance Dashboard</h2>

  <div class="search-box">
    <form method="get">
      <input type="text" name="search" placeholder="Search by name, role, or UID" value="<?= htmlspecialchars($search) ?>">
      <input type="submit" value="Search">
    </form>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Role</th>
      <th>UID</th>
      <th>Timestamp</th>
    </tr>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= $row['name'] ?? 'UNKNOWN' ?></td>
          <td><?= $row['role'] ?? '-' ?></td>
          <td><?= $row['uid'] ?></td>
          <td><?= $row['timestamp'] ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="5">No records found</td></tr>
    <?php endif; ?>
  </table>
</body>
</html>
<?php $conn->close(); ?>
