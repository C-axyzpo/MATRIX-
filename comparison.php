<?php
// comparison.php — Compare Attendance Between Two Groups
function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$conn = new mysqli("localhost","root","","attendance");
if($conn->connect_error) die("DB connection failed: ".$conn->connect_error);

// --- Inputs ---
$view = $_GET['view'] ?? 'week';
$grade1 = (int)($_GET['grade1'] ?? 11);
$section1 = trim($_GET['section1'] ?? '');
$grade2 = (int)($_GET['grade2'] ?? 12);
$section2 = trim($_GET['section2'] ?? '');
$start_date = $_GET['start'] ?? date('Y-m-d');

// --- Determine time range ---
$start = strtotime($start_date);
if ($view === 'day') {
    $end = $start;
    $rangeTitle = "Daily Comparison for ".date('M d, Y', $start);
} elseif ($view === 'week') {
    $end = strtotime("+6 days", $start);
    $rangeTitle = "Weekly Comparison (".date('M d', $start)." - ".date('M d, Y', $end).")";
} else {
    $end = strtotime("+1 month -1 day", $start);
    $rangeTitle = "Monthly Comparison (".date('F Y', $start).")";
}
$end_date = date('Y-m-d', $end);

// --- Function to get group data ---
function getGroupData($conn, $grade, $section, $start_date, $end_date) {
    $sql_users = "SELECT COUNT(*) AS total FROM users WHERE grade=?";
    $params = [$grade]; $types = "i";
    if ($section != '') { $sql_users .= " AND section=?"; $params[]=$section; $types.="s"; }

    $stmt = $conn->prepare($sql_users);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $enrolled = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $sql_logs = "SELECT DATE(timestamp) AS d, COUNT(DISTINCT uid) AS present
                 FROM logs WHERE DATE(timestamp) BETWEEN ? AND ?
                 GROUP BY d ORDER BY d";
    $stmt = $conn->prepare($sql_logs);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $res = $stmt->get_result();

    $data = [];
    while($r=$res->fetch_assoc()) $data[$r['d']] = (int)$r['present'];
    $stmt->close();

    return [$enrolled,$data];
}

// --- Get data for both groups ---
[$enrolled1,$data1] = getGroupData($conn,$grade1,$section1,$start_date,$end_date);
[$enrolled2,$data2] = getGroupData($conn,$grade2,$section2,$start_date,$end_date);

// --- Build unified labels ---
$labels=[]; $present1=[]; $present2=[];
for($t=$start;$t<=$end;$t+=86400){
    $d=date('Y-m-d',$t);
    $labels[]=$d;
    $present1[]=$data1[$d]??0;
    $present2[]=$data2[$d]??0;
}

// --- Summary stats ---
function calcStats($present,$enrolled){
    $total_days = count($present);
    $avg_present = $total_days>0?round(array_sum($present)/$total_days,1):0;
    $rate = $enrolled>0?round(($avg_present/$enrolled)*100,1):0;
    return [$avg_present,$rate];
}
[$avg1,$rate1]=calcStats($present1,$enrolled1);
[$avg2,$rate2]=calcStats($present2,$enrolled2);
$diff_rate = round($rate1 - $rate2,1);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Attendance Comparison</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
:root{
  --g1:#28a745; --g2:#007bff; --bg:#f4f9ff; --accent:#17a2b8;
}
body{
  font-family:'Segoe UI',Arial,sans-serif;
  background:var(--bg); margin:0; padding:20px; color:#222;
}
h1{margin:0 0 10px;}
h2{margin:10px 0 20px; font-size:18px; color:#444;}
form{
  display:flex; flex-wrap:wrap; gap:10px; align-items:center;
  background:#fff; padding:12px; border-radius:10px;
  box-shadow:0 6px 18px rgba(0,0,0,.05);
}
select,input[type=date],input[type=text]{
  padding:8px 10px; border-radius:8px; border:1px solid #ccc;
}
.chart-container{
  background:#fff; padding:20px; border-radius:12px;
  margin-top:20px; box-shadow:0 6px 18px rgba(0,0,0,.05);
}
.stats-box{
  display:flex; gap:20px; justify-content:center; margin-top:16px; flex-wrap:wrap;
}
.stat{
  background:#fff; padding:14px 22px; border-radius:10px;
  box-shadow:0 4px 14px rgba(0,0,0,.05);
  text-align:center; min-width:150px;
}
.stat strong{font-size:22px;display:block;margin-top:4px;color:var(--accent);}
.backlink{margin-bottom:12px;display:inline-block;color:var(--g2);text-decoration:none}
@media(max-width:600px){ form{flex-direction:column;align-items:stretch} }
</style>
</head>
<body>
<a href="statistics.php" class="backlink">← Back to Menu</a>
<h1>📈 Attendance Comparison Dashboard</h1>
<h2><?=esc($rangeTitle)?></h2>

<form id="filterForm">
  <label>View:</label>
  <select name="view" onchange="reloadData()">
    <option value="day" <?=($view==='day'?'selected':'')?>>Day</option>
    <option value="week" <?=($view==='week'?'selected':'')?>>Week</option>
    <option value="month" <?=($view==='month'?'selected':'')?>>Month</option>
  </select>

  <label>Group 1:</label>
  <select name="grade1" onchange="reloadData()">
    <?php for($g=7;$g<=12;$g++): ?>
      <option value="<?=$g?>" <?=($grade1==$g?'selected':'')?>>Grade <?=$g?></option>
    <?php endfor; ?>
  </select>
  <input type="text" name="section1" placeholder="Section (optional)" value="<?=esc($section1)?>" onchange="reloadData()">

  <label>Group 2:</label>
  <select name="grade2" onchange="reloadData()">
    <?php for($g=7;$g<=12;$g++): ?>
      <option value="<?=$g?>" <?=($grade2==$g?'selected':'')?>>Grade <?=$g?></option>
    <?php endfor; ?>
  </select>
  <input type="text" name="section2" placeholder="Section (optional)" value="<?=esc($section2)?>" onchange="reloadData()">

  <label>Start Date:</label>
  <input type="date" name="start" value="<?=esc($start_date)?>" onchange="reloadData()">
</form>

<div class="stats-box">
  <div class="stat">Group 1 Avg. Present<strong><?=$avg1?></strong><small>(<?=$rate1?>%)</small></div>
  <div class="stat">Group 2 Avg. Present<strong><?=$avg2?></strong><small>(<?=$rate2?>%)</small></div>
  <div class="stat">Rate Difference<strong><?=($diff_rate>0?"+":"")?><?=$diff_rate?>%</strong></div>
</div>

<div class="chart-container">
  <canvas id="compareChart"></canvas>
</div>

<script>
const labels = <?=json_encode($labels)?>;
const data1 = <?=json_encode($present1)?>;
const data2 = <?=json_encode($present2)?>;
const enrolled1 = <?=$enrolled1?>;
const enrolled2 = <?=$enrolled2?>;

const ctx = document.getElementById('compareChart');

new Chart(ctx, {
  type:'bar',
  data:{
    labels:labels,
    datasets:[
      {
        label:'Group 1 (<?=esc("Grade $grade1".($section1?" - $section1":""))?>)',
        data:data1,
        backgroundColor:'rgba(40,167,69,0.6)',
        borderColor:'#28a745',
        borderWidth:1
      },
      {
        label:'Group 2 (<?=esc("Grade $grade2".($section2?" - $section2":""))?>)',
        data:data2,
        backgroundColor:'rgba(0,123,255,0.6)',
        borderColor:'#007bff',
        borderWidth:1
      },
      {
        label:'Group 1 Attendance Rate (%)',
        data:data1.map(v => Math.round((v/enrolled1)*100)),
        type:'line',
        borderColor:'#20c997',
        borderWidth:2,
        tension:0.3,
        yAxisID:'y2',
        pointRadius:3
      },
      {
        label:'Group 2 Attendance Rate (%)',
        data:data2.map(v => Math.round((v/enrolled2)*100)),
        type:'line',
        borderColor:'#fd7e14',
        borderWidth:2,
        tension:0.3,
        yAxisID:'y2',
        pointRadius:3
      }
    ]
  },
  options:{
    responsive:true,
    interaction:{mode:'index',intersect:false},
    scales:{
      y:{
        beginAtZero:true,
        title:{display:true,text:'Number of Students'}
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
        text:'Group Attendance Comparison',
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
