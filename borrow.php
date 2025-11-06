<?php
require_once 'config.php';

// Fetch all laptops
$laptops = $conn->query("SELECT * FROM laptops ORDER BY laptop_no ASC");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Laptop Borrowing</title>
  <meta http-equiv="refresh" content="2"> <!-- Auto-refresh every 2 seconds -->
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f9ff;
      margin: 0;
      padding: 20px;
      text-align: center;
    }
    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #007BFF;
      color: white;
      padding: 12px 25px;
      border-radius: 8px;
      max-width: 1200px;
      margin: 0 auto 25px auto;
    }
    header h1 {
      margin: 0;
      font-size: 24px;
    }
    .back {
      background: #6c757d;
      color: #fff;
      border: none;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      transition: background 0.3s;
      font-size: 15px;
    }
    .back:hover {
      background: #5a6268;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .laptop {
      padding: 15px;
      border-radius: 10px;
      background: #fff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      text-align: center;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .laptop:hover {
      transform: scale(1.05);
      box-shadow: 0 6px 14px rgba(0,0,0,0.08);
    }
    .status {
      font-weight: bold;
      margin: 8px 0;
      padding: 6px 10px;
      border-radius: 6px;
      color: #fff;
      display: inline-block;
      width: 90px;
    }
    .available {
      background: #28a745;
    }
    .borrowed {
      background: #dc3545;
    }
    .info {
      font-size: 14px;
      color: #555;
      margin-top: 5px;
    }
    footer {
      margin-top: 30px;
      font-size: 13px;
      color: #666;
    }
  </style>
</head>
<body>

  <header>
    <h1>💻 Laptop Borrowing</h1>
    <a href="menu1_5.php" class="back">⬅ Back to Menu</a>
  </header>

  <div class="grid">
    <?php if($laptops && $laptops->num_rows > 0): ?>
      <?php while($row = $laptops->fetch_assoc()): ?>
        <?php
          $statusClass = ($row['status'] == 'Available') ? 'available' : 'borrowed';
          $displayName = $row['borrower_name'] ?? '-';
          $displayTime = $row['borrow_time'] ?? '-';
        ?>
        <div class="laptop">
          <div><strong>Laptop #<?= htmlspecialchars($row['laptop_no']) ?></strong></div>
          <div class="status <?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></div>
          <div class="info">Name: <?= htmlspecialchars($displayName) ?></div>
          <div class="info">Time: <?= htmlspecialchars($displayTime) ?></div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p>No laptops found.</p>
    <?php endif; ?>
  </div>

  <footer>Auto-refreshing every 2 seconds · <?= date("h:i:s A") ?></footer>

</body>
</html>
