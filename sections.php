<?php
// sections.php
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

$grade = isset($_GET['grade']) ? (int)$_GET['grade'] : null;
$sections = [];

if (in_array($grade, [7,8,9,10])) {
    for ($i=1; $i<=15; $i++) $sections[] = "{$grade}-{$i}";
} elseif ($grade == 11 || $grade == 12) {
    $sections = ["ABM", "HUMMS-A", "HUMMS-B", "GAS-A", "GAS-B"];
} else {
    header("Location: menu.php"); exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sections - Grade <?=esc($grade)?></title>
  <style>
    body{font-family:Arial,sans-serif;background:#f7fbff;padding:22px}
    h2{text-align:center}
    .box{max-width:900px;margin:20px auto;background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
    .grid{display:flex;flex-wrap:wrap;gap:12px;justify-content:center}
    .sec{display:inline-block;width:150px;height:90px;background:#28a745;color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;text-decoration:none;font-weight:700}
    .sec:hover{background:#1e7e34}
  </style>
</head>
<body>
  <div class="box">
    <h2>Sections for Grade <?=esc($grade)?></h2>
    <div class="grid">
      <?php foreach($sections as $s): ?>
        <a class="sec" href="section_dashboard.php?grade=<?=urlencode($grade)?>&section=<?=urlencode($s)?>"><?=esc($s)?></a>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>
