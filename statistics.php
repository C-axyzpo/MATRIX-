<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Statistics</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f7fbff;text-align:center;padding:30px}
    h2{margin-bottom:30px}
    .grid{display:flex;flex-wrap:wrap;gap:20px;justify-content:center}
    .btn{width:200px;height:120px;display:flex;align-items:center;justify-content:center;
         font-size:22px;font-weight:bold;color:#fff;background:#007bff;border-radius:12px;
         text-decoration:none;box-shadow:0 4px 10px rgba(0,0,0,0.1);transition:0.2s}
    .btn:hover{background:#0056b3;transform:translateY(-3px)}
    .back{display:inline-block;margin:30px;padding:12px 20px;background:#6c757d;
          color:#fff;border-radius:6px;text-decoration:none}
    .back:hover{background:#5a6268}
  </style>
</head>
<body>
  <h2>Statistics Dashboard</h2>

  <div class="grid">
    <a class="btn" href="percentage.php">📊 Percentage</a>
    <a class="btn" href="trends.php">📈 Trends</a>
    <a class="btn" href="comparison.php">⚖️ Comparison</a>
    <a class="btn" href="streak.php">🔥 Streak</a>
  </div>

  <!-- Back button -->
  <a href="menu2.php" class="back">⬅ Back</a>
</body>
</html>
