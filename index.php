<?php
// index.php
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Welcome - CAYBIGAN</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f2f6fb;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
    .card{background:white;padding:40px;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.08);text-align:center}
    h1{margin:0 0 12px}
    p{color:#555;margin-bottom:24px}
    .btn{display:inline-block;padding:12px 28px;background:#007bff;color:#fff;border-radius:8px;text-decoration:none;font-weight:700}
    .btn:hover{background:#0056b3}
  </style>
</head>
<body>
  <div class="card">
    <h1>Welcome CAYBIGAN</h1>
    <p>School Attendance Portal</p>
    <form action="menu.php" method="get">
      <button class="btn" type="submit">Continue</button>
    </form>
  </div>
</body>
</html>
