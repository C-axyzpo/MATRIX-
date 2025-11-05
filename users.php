<?php
function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) { die("DB connection failed: " . $conn->connect_error); }

$grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';
if ($grade === 0 || $section=='') die("Missing grade or section.");

$sql = "SELECT id, uid, name, gender 
        FROM users 
        WHERE grade=$grade AND section='$section' 
        ORDER BY name";
$result = $conn->query($sql);

$male = [];
$female = [];

if ($result && $result->num_rows > 0) {
    while($row=$result->fetch_assoc()) {
        if (strtolower($row['gender']) === 'male') {
            $male[] = $row;
        } else {
            $female[] = $row;
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Users - <?=esc($section)?></title>
  <style>
    body{font-family:Arial,sans-serif;background:#f4f9ff;margin:0;padding:0}
    header{background:#007bff;color:#fff;padding:15px;text-align:center}
    .container{display:flex;gap:20px;justify-content:center;flex-wrap:wrap;margin:20px}
    .box{flex:1;min-width:400px;background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
    h2{margin:0 0 10px;color:#007bff;text-align:center}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{padding:10px;border:1px solid #ddd;text-align:left}
    th{background:#007bff;color:#fff}
    .back{display:inline-block;margin:15px;padding:10px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none}
    .back:hover{background:#5a6268}
  </style>
</head>
<body>
  <header>
    <h1>Users - <?=esc($grade . " " . $section)?></h1>
  </header>

  <a href="section_dashboard.php?grade=<?=$grade?>&section=<?=urlencode($section)?>" class="back">⬅ Back</a>

  <div class="container">
    <!-- Male Table -->
    <div class="box">
      <h2>Male</h2>
      <table>
        <tr><th>ID</th><th>UID</th><th>Name</th></tr>
        <?php if (!empty($male)): ?>
          <?php foreach($male as $row): ?>
            <tr>
              <td><?=esc($row['id'])?></td>
              <td><?=esc($row['uid'])?></td>
              <td><?=esc($row['name'])?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="3">No male students found.</td></tr>
        <?php endif; ?>
      </table>
    </div>

    <!-- Female Table -->
    <div class="box">
      <h2>Female</h2>
      <table>
        <tr><th>ID</th><th>UID</th><th>Name</th></tr>
        <?php if (!empty($female)): ?>
          <?php foreach($female as $row): ?>
            <tr>
              <td><?=esc($row['id'])?></td>
              <td><?=esc($row['uid'])?></td>
              <td><?=esc($row['name'])?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="3">No female students found.</td></tr>
        <?php endif; ?>
      </table>
    </div>
  </div>
</body>
</html>
