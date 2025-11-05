<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Select Grade</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f7fbff;text-align:center;padding:30px}
    h2{margin-bottom:20px}
    .section{margin:30px 0}
    .grid{display:flex;flex-wrap:wrap;gap:20px;justify-content:center}
    .btn{width:160px;height:100px;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:bold;color:#fff;background:#28a745;border-radius:12px;text-decoration:none}
    .btn:hover{background:#1e7e34}
    .back{display:inline-block;margin:15px;padding:10px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none}
    .back:hover{background:#5a6268}
    .stat{display:inline-block;margin:20px auto;padding:20px 40px;font-size:22px;font-weight:bold;color:#fff;background:#007bff;border-radius:12px;text-decoration:none}
    .stat:hover{background:#0056b3}
  </style>
</head>
<body>
  <h2>Select Grade</h2>

  <div class="section">
    <h3>Junior High School</h3>
    <div class="grid">
      <a class="btn" href="grade_dashboard.php?grade=7">Grade 7</a>
      <a class="btn" href="grade_dashboard.php?grade=8">Grade 8</a>
      <a class="btn" href="grade_dashboard.php?grade=9">Grade 9</a>
      <a class="btn" href="grade_dashboard.php?grade=10">Grade 10</a>
    </div>
  </div>

  <div class="section">
    <h3>Senior High School</h3>
    <div class="grid">
      <a class="btn" href="grade_dashboard.php?grade=11">Grade 11</a>
      <a class="btn" href="grade_dashboard.php?grade=12">Grade 12</a>
    </div>
  </div>

  <!-- Statistics button -->
  <a href="statistics.php" class="stat">📊 STATISTICS</a>

  <br>

  <!-- Back button to Main Menu -->
  <a href="menu1_5.php" class="back">⬅ Back to Main Menu</a>

</body>
</html>
