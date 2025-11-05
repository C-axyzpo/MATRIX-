<?php
function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

$grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 0;
if ($grade === 0) die("No grade selected. Add ?grade=11 to the URL.");

// Build section list
$sections = [];
if (in_array($grade, [7,8,9,10])) {
    for ($i=1; $i<=15; $i++) $sections[] = "{$grade}-{$i}";
} elseif ($grade == 11 || $grade == 12) {
    $sections = ["ABM","HUMMS-A","HUMMS-B","GAS-A","GAS-B"];
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Grade <?=esc($grade)?> Sections</title>
  <style>
    body{font-family:Arial,sans-serif;background:#f4f9ff;margin:0;padding:0}
    header{background:#007bff;color:#fff;padding:15px;text-align:center}
    .box{max-width:1100px;margin:20px auto;background:#fff;padding:18px;
         border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
    .grid{display:flex;flex-wrap:wrap;gap:12px;justify-content:center;margin-top:20px}
    .sec{width:150px;height:90px;background:#28a745;color:#fff;border-radius:10px;
         display:flex;align-items:center;justify-content:center;
         text-decoration:none;font-weight:700;font-size:18px}
    .sec:hover{background:#1e7e34}
    .back{display:inline-block;margin:15px;padding:10px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none}
    .back:hover{background:#5a6268}
  </style>
</head>
<body>
  <header>
    <h1>Grade <?=esc($grade)?> Sections</h1>
  </header>

  <!-- Back button to GRADE MENU -->
  <a href="menu2.php" class="back">⬅ Back to Grade Menu</a>

  <div class="box">
    <h2>Select a Section</h2>
    <div class="grid">
      <?php foreach($sections as $s): ?>
        <a class="sec" href="section_dashboard.php?grade=<?=urlencode($grade)?>&section=<?=urlencode($s)?>">
          <?=esc($s)?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
