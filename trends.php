<?php
// trends.php — Ultimate Attendance Trends Dashboard
function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$conn = new mysqli("localhost","root","","attendance");
if($conn->connect_error) die("DB connection failed: ".$conn->connect_error);

// --- Inputs ---
$view = $_GET['view'] ?? 'week'; // day | week | month
$grade = (int)($_GET['grade'] ?? 11);
$section = trim($_GET['section'] ?? '');
$start_date = $_GET['start'] ?? date('Y-m-d');

// --- Get enrolled count ---
$enrolled_sql = "SELECT COUNT(*) AS total FROM users WHERE grade=?";
$params = [$grade]; $types = "i";
if ($section != '') { $enrolled_sql .= " AND section=?"; $params[]=$section; $types.="s"; }
$stmt = $conn->prepare($enrolled_sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$enrolled = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// --- Determine time range ---
$start = strtotime($start_date);
if ($view === 'day') { $end = $start; $rangeTitle = "Daily Trend for ".date('M d, Y',$start); }
elseif ($view === 'week') { $end = strtotime("+6 days",$start); $rangeTitle = "Weekly Trend (".date('M d',$start)." - ".date('M d, Y',$end).")"; }
else { $end = strtotime("+1 month -1 day",$start); $rangeTitle = "Monthly Trend (".date('F Y',$start).")"; }
$end_date = date('Y-m-d',$end);

// --- Fetch logs ---
$sql = "SELECT DATE(timestamp) AS d, COUNT(DISTINCT uid) AS present
        FROM logs
        WHERE DATE(timestamp) BETWEEN ? AND ?
        GROUP BY d ORDER BY d";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while($r = $res->fetch_assoc()) $data[$r['d']] = (int)$r['present'];
$stmt->close();

// --- Build arrays ---
$labels = []; $present_values = [];
for($t=$start;$t<=$end;$t+=86400){
  $d = date('Y-m-d',$t);
  $labels[] = $d;
  $present_values[] = $data[$d] ?? 0;
}

// --- Averages ---
$total_present = array_sum($present_values);
$total_days = count($present_values);
$avg_present = $total_days>0 ? round($total_present/$total_days,1) : 0;
$avg_rate = $enrolled>0 ? round(($avg_present/$enrolled)*100,1) : 0;
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Trends Dashboard</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root { --accent:#007bff; --green:#28a745; --yellow:#ffc107; --red:#dc3545; --aqua:#17a2b8; --bg:#f4f9ff; }
body { font-family:'Segoe UI',Arial,sans-serif; margin:0; padding:20px; background:var(--bg); color:#222; }
h1 { margin:0 0 10px; }
h2 { margin:10px 0 20px; font-size:18px; color:#444; }
form {
  display:flex; flex-wrap:wrap; gap:10px; align-items:center;
  background:#fff; padding:12px; border-radius:10px;
  box-shadow:0 6px 18px rgba(0,0,0,.05);
}
select, input[type=date], input[type=text] {
  padding:8px 10px; border-radius:8px; border:1px solid #ccc;
}
button {
  background:var(--accent); color:white; font-weight:600;
  border:none; border-radius:8px; padding:8px 14px; cursor:pointer;
}
button:hover { opacity:0.9; }
.chart-container {
  background:#fff; padding:20px; border-radius:12px;
  margin-top:20px; box-shadow:0 6px 18px rgba(0,0,0,.05);
}
.stats-box { display:flex; gap:20px; justify-content:center; margin-top:16px; flex-wrap:wrap; }
.stat {
  background:#fff; padding:14px 22px; border-radius:10px;
  box-shadow:0 4px 14px rgba(0,0,0,.05);
  text-align:center; min-width:140px;
}
.stat strong { font-size:22px; display:block; margin-top:4px; color:var(--green); }
.backlink{margin-bottom:12px;display:inline-block;color:var(--accent);text-decoration:none}
@media(max-width:600px){ form{flex-direction:column;align-items:stretch} }
</style>
</head>
<body>
<a href="statistics.php" class="backlink">← Back to Menu</a>
<h1>📊 Attendance Trends Dashboard</h1>
<h2><?=esc($rangeTitle)?></h2>

<form id="filterForm">
  <label>View:</label>
  <select name="view" onchange="reloadData()">
    <option value="day" <?=($view==='day'?'selected':'')?>>Day</option>
    <option value="week" <?=($view==='week'?'selected':'')?>>Week</option>
    <option value="month" <?=($view==='month'?'selected':'')?>>Month</option>
  </select>

  <label>Grade:</label>
  <select name="grade" onchange="reloadData()">
    <?php for($g=7;$g<=12;$g++): ?>
      <option value="<?=$g?>" <?=($grade==$g?'selected':'')?>><?=$g?></option>
    <?php endfor; ?>
  </select>

  <label>Section:</label>
  <input type="text" name="section" value="<?=esc($section)?>" placeholder="Optional" onchange="reloadData()">

  <label>Start Date:</label>
  <input type="date" name="start" value="<?=esc($start_date)?>" onchange="reloadData()">
</form>

<div class="stats-box">
  <div class="stat">Enrolled<strong><?=$enrolled?></strong></div>
  <div class="stat">Avg. Present<strong><?=$avg_present?></strong></div>
  <div class="stat">Attendance Rate<strong><?=$avg_rate?>%</strong></div>
</div>

<div class="chart-container">
  <canvas id="trendChart"></canvas>
</div>

<script>
const ctx = document.getElementById('trendChart');
const labels = <?=json_encode($labels)?>;
const presentData = <?=json_encode($present_values)?>;
const enrolled = <?=$enrolled?>;
const avgPresent = <?=$avg_present?>;

// Build datasets
const datasets = [
  {
    label:'Present',
    data:presentData,
    backgroundColor:'rgba(40,167,69,0.7)',
    borderColor:'#28a745',
    borderWidth:1,
    yAxisID:'y'
  },
  {
    label:'Enrolled',
    data:Array(labels.length).fill(enrolled),
    type:'line',
    borderColor:'#007bff',
    borderWidth:2,
    tension:0.2,
    pointRadius:3,
    yAxisID:'y'
  },
  {
    label:'Average Present',
    data:Array(labels.length).fill(avgPresent),
    type:'line',
    borderColor:'#ffc107',
    borderDash:[6,6],
    borderWidth:2,
    tension:0.2,
    pointRadius:0,
    yAxisID:'y'
  },
  {
    label:'Target (95%)',
    data:Array(labels.length).fill(enrolled*0.95),
    type:'line',
    borderColor:'#dc3545',
    borderDash:[8,4],
    borderWidth:2,
    tension:0.2,
    pointRadius:0,
    yAxisID:'y'
  },
  {
    label:'Attendance Rate (%)',
    data:presentData.map(v => Math.round((v/enrolled)*100)),
    type:'line',
    borderColor:'#17a2b8',
    borderWidth:2,
    tension:0.3,
    fill:false,
    yAxisID:'y2',
    pointRadius:3
  }
];

const chart = new Chart(ctx,{
  type:'bar',
  data:{labels:labels,datasets:datasets},
  options:{
    responsive:true,
    interaction:{mode:'index',intersect:false},
    scales:{
      y:{
        beginAtZero:true,
        title:{display:true,text:'Number of Students'},
        suggestedMax:enrolled
      },
      y2:{
        beginAtZero:true,
        position:'right',
        title:{display:true,text:'Attendance Rate (%)'},
        grid:{drawOnChartArea:false}
      }
    },
    plugins:{
      legend:{position:'top'},
      title:{
        display:true,
        text:'Attendance vs Enrolled, Average, and Target',
        font:{size:16,weight:'bold'}
      }
    }
  }
});

function reloadData(){
  const params=new URLSearchParams(new FormData(document.getElementById('filterForm')));
  window.location='?'+params.toString();
}
</script>
</body>
</html>
