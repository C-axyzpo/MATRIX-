<?php
// streak.php — Attendance Streak + Reward System + Archive (No FPDF)

$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) die("DB Connection failed: " . $conn->connect_error);

// helper
function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function redirect($q=''){ header('Location: streak.php'.($q?"?$q":'')); exit; }

// Reward thresholds
$rewards = [
    3  => ["Bronze Certificate", "bronze"],
    5  => ["Silver Certificate", "silver"],
    7  => ["Gold Certificate", "gold"],
    10 => ["Free Meal Coupon", "meal"],
    15 => ["VIP Attendance Badge", "vip"]
];

// ---------- Actions ----------
if (isset($_GET['claim'])) {
    $uid = (int)$_GET['claim'];
    $reward = $conn->real_escape_string($_GET['reward'] ?? '');
    $conn->query("UPDATE users SET reward_claimed='$reward' WHERE uid=$uid");
    redirect();
}

// Archive & reset
if (isset($_GET['archive'])) {
    $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n', strtotime('first day of last month'));
    $year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y', strtotime('first day of last month'));
    $topN  = isset($_GET['top'])   ? (int)$_GET['top']   : 10;

    $start_m = sprintf("%04d-%02d-01", $year, $month);
    $end_m = date('Y-m-t', strtotime($start_m));

    $sql = "
      SELECT u.uid, u.name, u.grade, u.section, COUNT(DISTINCT DATE(l.timestamp)) AS present_count
      FROM users u
      LEFT JOIN logs l ON u.uid = l.uid AND DATE(l.timestamp) BETWEEN ? AND ?
      GROUP BY u.uid
      ORDER BY present_count DESC
      LIMIT ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $start_m, $end_m, $topN);
    $stmt->execute();
    $res = $stmt->get_result();

    $insert = $conn->prepare("INSERT INTO streak_archive (uid,name,grade,section,streak,reward,month,year) VALUES (?,?,?,?,?,?,?,?)");
    while($r = $res->fetch_assoc()) {
        $uid = $r['uid'];
        $present_count = (int)$r['present_count'];
        $rewardLabel = '';
        foreach ($rewards as $days => $info) {
            if ($present_count >= $days) $rewardLabel = $info[0];
        }
        $insert->bind_param('isissiii', $uid, $r['name'], $r['grade'], $r['section'], $present_count, $rewardLabel, $month, $year);
        $insert->execute();
    }
    $insert->close();
    $stmt->close();

    $conn->query("UPDATE users SET reward_claimed = ''");
    redirect();
}

$view_archive = isset($_GET['view_archive']);

// ---------- Leaderboard ----------
$today = date('Y-m-d');
$gradeFilter = isset($_GET['grade']) && $_GET['grade']!=='' ? (int)$_GET['grade'] : null;
$sectionFilter = isset($_GET['section']) && $_GET['section']!=='' ? $conn->real_escape_string($_GET['section']) : null;

$users_sql = "SELECT u.uid, u.name, u.grade, u.section, IFNULL(u.reward_claimed,'') AS reward_claimed
              FROM users u ORDER BY u.grade, u.section, u.name";
$ures = $conn->query($users_sql);

$students = [];
while($row = $ures->fetch_assoc()) {
    if ($gradeFilter !== null && (int)$row['grade'] !== $gradeFilter) continue;
    if ($sectionFilter !== null && stripos($row['section'], $sectionFilter) === false) continue;

    $uid = (int)$row['uid'];
    $dates = [];
    $lres = $conn->query("SELECT DISTINCT DATE(timestamp) AS d FROM logs WHERE uid = $uid ORDER BY d DESC");
    while($lr = $lres->fetch_assoc()) $dates[] = $lr['d'];
    $prev = $today; $streak = 0;
    foreach($dates as $d) {
        if (date('Y-m-d', strtotime($prev.' -1 day')) == $d) {
            $streak++; $prev = $d;
        } elseif ($d == $today) {
            $streak++; $prev = $d;
        } else break;
    }

    $rewardLabel = "No Reward Yet"; $badge = 'noreward';
    foreach ($rewards as $days => $info) { 
        if ($streak >= $days) { $rewardLabel = $info[0]; $badge = $info[1]; } 
    }

    $students[] = [
        'uid'=>$uid, 'name'=>$row['name'], 'grade'=>$row['grade'],
        'section'=>$row['section'], 'streak'=>$streak, 'reward'=>$rewardLabel,
        'badge'=>$badge, 'claimed'=>$row['reward_claimed']
    ];
}
usort($students, fn($a,$b)=>$b['streak']<=>$a['streak'] ?: strcmp($a['name'],$b['name']));

// ---------- Archive Viewer ----------
$archives_months = [];
if ($view_archive) {
    $amq = $conn->query("SELECT DISTINCT month, year FROM streak_archive ORDER BY year DESC, month DESC");
    while($r = $amq->fetch_assoc()) $archives_months[] = $r;
    $arch_entries = [];
    if (isset($_GET['amonth']) && isset($_GET['ayear'])) {
        $amonth = (int)$_GET['amonth']; $ayear = (int)$_GET['ayear'];
        $stmt = $conn->prepare("SELECT * FROM streak_archive WHERE month=? AND year=? ORDER BY streak DESC");
        $stmt->bind_param('ii',$amonth,$ayear);
        $stmt->execute();
        $res = $stmt->get_result();
        while($r = $res->fetch_assoc()) $arch_entries[] = $r;
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Streak System</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
:root{
  --bg:#f5f9ff; --card:#fff; --primary:#007bff;
  --gold:#ffb300; --silver:#c0c0c0; --bronze:#cd7f32;
  --meal:#28a745; --vip:#6f42c1;
}
body{font-family:'Segoe UI',Arial,sans-serif;background:var(--bg);margin:0;padding:20px;color:#222}
.header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px}
.btn{background:var(--primary);color:#fff;padding:8px 12px;border-radius:8px;text-decoration:none;font-weight:600}
.btn.secondary{background:#6c757d}
.card{background:var(--card);padding:14px;border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.06);margin-top:14px}
table{width:100%;border-collapse:collapse;margin-top:12px;border-radius:8px;overflow:hidden}
th,td{padding:10px;border-bottom:1px solid #eee;text-align:left}
th{background:var(--primary);color:#fff}
tr:hover{background:#f1f7ff}
.badge{display:inline-block;padding:6px 10px;border-radius:8px;color:#fff;font-weight:700}
.bronze{background:var(--bronze)} .silver{background:var(--silver);color:#000}
.gold{background:var(--gold)} .meal{background:var(--meal)} .vip{background:var(--vip)} .noreward{background:#ccc;color:#000}
.claimed{opacity:0.6}
.small{font-size:13px;color:#555}
.progress{height:8px;background:#eee;border-radius:6px;overflow:hidden;width:160px}
.progress-bar{height:100%;background:var(--primary)}
</style>
</head>
<body>

<div class="header">
  <h2>🏅 Attendance Streak & Rewards System</h2>
  <div>
    <a href="statistics.php" class="btn">← Back</a>
    <a href="?archive=1" class="btn secondary">Archive & Reset</a>
    <a href="?view_archive=1" class="btn">View Archive</a>
  </div>
</div>

<div class="card">
  <form method="get">
    <label>Grade:
      <select name="grade">
        <option value="">All</option>
        <?php for($g=7;$g<=12;$g++): ?>
        <option value="<?=$g?>" <?=isset($_GET['grade'])&&$_GET['grade']==$g?'selected':''?>><?=$g?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Section: <input type="text" name="section" value="<?=esc($_GET['section']??'')?>"></label>
    <button class="btn">Filter</button>
  </form>
</div>

<div class="card">
  <h3>🔥 Current Leaderboard</h3>
  <table>
    <tr><th>#</th><th>Name</th><th>Grade</th><th>Section</th><th>Streak</th><th>Progress</th><th>Reward</th><th>Claimed</th><th>Action</th></tr>
    <?php $rank=1; foreach($students as $s): 
      $needed = max(array_keys($rewards)); 
      $progress = min(100, ($s['streak'] / $needed) * 100);
    ?>
      <tr class="<?=($s['claimed']?'claimed':'')?>">
        <td><?=$rank++?></td>
        <td><?=esc($s['name'])?></td>
        <td><?=$s['grade']?></td>
        <td><?=esc($s['section'])?></td>
        <td><b><?=$s['streak']?></b> days</td>
        <td><div class="progress"><div class="progress-bar" style="width:<?=$progress?>%"></div></div></td>
        <td><span class="badge <?=$s['badge']?>"><?=esc($s['reward'])?></span></td>
        <td><?=esc($s['claimed'] ?: '—')?></td>
        <td>
          <?php if($s['reward']!=="No Reward Yet" && !$s['claimed']): ?>
            <a class="btn" href="?claim=<?=$s['uid']?>&reward=<?=urlencode($s['reward'])?>">Claim</a>
          <?php else: ?><span class="small">—</span><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<?php if($view_archive): ?>
<div class="card">
  <h3>📚 Archived Top Streakers</h3>
  <form method="get">
    <input type="hidden" name="view_archive" value="1">
    <label>Month:
      <select name="amonth">
        <?php for($m=1;$m<=12;$m++): ?>
        <option value="<?=$m?>" <?=isset($_GET['amonth'])&&$_GET['amonth']==$m?'selected':''?>><?=date('F', mktime(0,0,0,$m,1))?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Year:
      <select name="ayear">
        <?php for($y=date('Y');$y>=date('Y')-5;$y--): ?>
        <option value="<?=$y?>" <?=isset($_GET['ayear'])&&$_GET['ayear']==$y?'selected':''?>><?=$y?></option>
        <?php endfor; ?>
      </select>
    </label>
    <button class="btn">Show</button>
  </form>

  <?php if(!empty($archives_months)): ?>
    <div style="margin-top:10px;">
      <strong>Available:</strong>
      <?php foreach($archives_months as $am): ?>
        <a href="?view_archive=1&amonth=<?=$am['month']?>&ayear=<?=$am['year']?>" class="btn small"><?=date('F', mktime(0,0,0,$am['month'],1)).' '.$am['year']?></a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if(isset($arch_entries)&&!empty($arch_entries)): ?>
    <table>
      <tr><th>#</th><th>Name</th><th>Grade</th><th>Section</th><th>Days</th><th>Reward</th><th>Archived</th></tr>
      <?php $i=1; foreach($arch_entries as $a): ?>
      <tr>
        <td><?=$i++?></td>
        <td><?=esc($a['name'])?></td>
        <td><?=$a['grade']?></td>
        <td><?=esc($a['section'])?></td>
        <td><?=$a['streak']?></td>
        <td><?=esc($a['reward'])?></td>
        <td><?=$a['archived_at']?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php elseif(isset($_GET['amonth'])): ?>
    <p>No archived data found.</p>
  <?php endif; ?>
</div>
<?php endif; ?>

</body>
</html>
