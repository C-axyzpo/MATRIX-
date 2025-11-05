<?php
function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) { 
    die("DB connection failed: " . $conn->connect_error); 
}

$grade   = isset($_GET['grade']) ? (int)$_GET['grade'] : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';
if ($grade === 0 || $section=='') die("Missing grade or section.");

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'day'; // default daily
$today  = date("Y-m-d");

// Kunin lahat ng students sa section
$sql_users = "SELECT id, uid, name FROM users WHERE grade=$grade AND section='$section' ORDER BY name ASC";
$users = $conn->query($sql_users);
if (!$users) {
    die("Query failed: " . $conn->error);
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Calculation - <?=esc($section)?></title>
  <style>
    body{font-family:Arial,sans-serif;background:#f4f9ff;margin:0;padding:0}
    header{background:#007bff;color:#fff;padding:15px;text-align:center}
    nav{display:flex;justify-content:center;gap:15px;margin:15px}
    nav a{padding:8px 14px;border-radius:6px;text-decoration:none;color:#007bff;font-weight:600}
    nav a.active, nav a:hover{background:#007bff;color:#fff}
    .box{max-width:1000px;margin:20px auto;background:#fff;padding:18px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
    table{width:100%;border-collapse:collapse;margin-top:15px}
    th,td{padding:10px;border:1px solid #ddd;text-align:left}
    th{background:#007bff;color:#fff}
    .back{display:inline-block;margin:15px;padding:10px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none}
    .back:hover{background:#5a6268}
    .present{color:green;font-weight:bold}
    .late{color:orange;font-weight:bold}
    .absent{color:red;font-weight:bold}
  </style>
</head>
<body>
  <header>
    <h1>Attendance Calculation - <?=esc($section)?></h1>
  </header>

  <!-- Back button -->
  <a href="section_dashboard.php?grade=<?=$grade?>&section=<?=urlencode($section)?>" class="back">⬅ Back</a>

  <!-- Filter navigation -->
  <nav>
    <a href="?grade=<?=$grade?>&section=<?=urlencode($section)?>&filter=day" class="<?=($filter=='day'?'active':'')?>">Daily</a>
    <a href="?grade=<?=$grade?>&section=<?=urlencode($section)?>&filter=week" class="<?=($filter=='week'?'active':'')?>">Weekly</a>
    <a href="?grade=<?=$grade?>&section=<?=urlencode($section)?>&filter=month" class="<?=($filter=='month'?'active':'')?>">Monthly</a>
    <a href="?grade=<?=$grade?>&section=<?=urlencode($section)?>&filter=year" class="<?=($filter=='year'?'active':'')?>">Yearly</a>
  </nav>

  <div class="box">
    <h2><?=ucfirst($filter)?> Records (<?=$today?>)</h2>
    <table>
      <tr><th>Date</th><th>Time</th><th>UID</th><th>Name</th><th>Status</th></tr>
      <?php if ($users && $users->num_rows > 0): ?>
        <?php while($user = $users->fetch_assoc()): ?>
          <?php
            $uid = $user['uid'];
            $name = $user['name'];

            // Hanapin logs ngayong araw
            $sql_log = "SELECT TIME(`timestamp`) as time_in 
                        FROM logs 
                        WHERE uid='$uid' AND grade=$grade AND section='$section' 
                              AND DATE(`timestamp`)='$today'
                        ORDER BY `timestamp` ASC 
                        LIMIT 1";
            $res_log = $conn->query($sql_log);

            if ($res_log && $res_log->num_rows > 0) {
                $log = $res_log->fetch_assoc();
                $time_in = $log['time_in'];

                if (strtotime($time_in) > strtotime("12:20:00")) {
                    $status = "<span class='late'>Late</span>";
                } else {
                    $status = "<span class='present'>Present</span>";
                }
            } else {
                $time_in = "-";
                $status = "<span class='absent'>Absent</span>";
            }
          ?>
          <tr>
            <td><?=$today?></td>
            <td><?=esc($time_in)?></td>
            <td><?=esc($uid)?></td>
            <td><?=esc($name)?></td>
            <td><?=$status?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="5">No students found in this section.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</body>
</html>
