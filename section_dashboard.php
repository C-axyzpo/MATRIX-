<?php
function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

$grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';
if ($grade === 0 || $section=='') die("Missing grade or section.");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Section <?=esc($grade . " " . $section)?> Dashboard</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f4f9ff;margin:0;padding:0}
    header{background:#007bff;color:#fff;padding:15px;text-align:center}
    nav{display:flex;justify-content:center;gap:20px;background:#e9f2ff;padding:10px}
    nav a{padding:10px 18px;text-decoration:none;color:#007bff;font-weight:600;border-radius:6px}
    nav a:hover{background:#007bff;color:#fff}
    .box{max-width:800px;margin:20px auto;background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
    .back{display:inline-block;margin:15px;padding:10px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none}
    .back:hover{background:#5a6268}
  </style>
</head>
<body>
  <header>
    <h1><?=esc($grade . " " . $section)?> Dashboard</h1>
  </header>

  <!-- Back button to Grade Dashboard -->
  <a href="grade_dashboard.php?grade=<?=$grade?>" class="back">⬅ Back to Grade <?=esc($grade)?></a>

  <nav>
    <a href="users.php?grade=<?=$grade?>&section=<?=urlencode($section)?>">Users</a>
    <a href="register.php?grade=<?=$grade?>&section=<?=urlencode($section)?>">Register New User</a>
    <a href="calculation.php?grade=<?=$grade?>&section=<?=urlencode($section)?>">Calculation</a>
  </nav>
</body>
</html>
