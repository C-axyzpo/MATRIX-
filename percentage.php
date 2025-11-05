<?php
// percentage.php (updated)
// Keep your details: DB 'attendance', tables 'users' (uid,name,grade,section,gender) and 'logs' (uid,timestamp,...)

function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) die("DB connection failed: " . $conn->connect_error);

// ---- inputs ----
$scope = isset($_GET['scope']) ? $_GET['scope'] : 'section'; // 'section' | 'grade' | 'all'
$grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 11;
$section = isset($_GET['section']) ? $_GET['section'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); // YYYY-MM-DD
$cutoff_input = isset($_GET['cutoff']) ? $_GET['cutoff'] : '12:20'; // HH:MM
$exportCsv = isset($_GET['export']) && $_GET['export']=='1';

// Normalize/validate
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $date = date('Y-m-d');
if (!preg_match('/^\d{1,2}:\d{2}$/', $cutoff_input)) $cutoff_input = '12:20';
$cutoff_seconds = strtotime($cutoff_input . ':00') - strtotime('TODAY'); // seconds from midnight; will use strtotime on times

// fetch list of sections for selected grade (for section selector)
$sections = [];
$stmtS = $conn->prepare("SELECT DISTINCT section FROM users WHERE grade = ? ORDER BY section");
$stmtS->bind_param("i", $grade);
$stmtS->execute();
$resS = $stmtS->get_result();
while ($r = $resS->fetch_assoc()) $sections[] = $r['section'];
$stmtS->close();

// Build user list query depending on scope
if ($scope === 'section') {
    $sql = "SELECT u.uid, u.name, u.gender, MIN(l.timestamp) AS ts
            FROM users u
            LEFT JOIN logs l ON l.uid = u.uid AND DATE(l.timestamp) = ?
            WHERE u.grade = ? AND u.section = ?
            GROUP BY u.uid
            ORDER BY u.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sis', $date, $grade, $section);
} elseif ($scope === 'grade') {
    $sql = "SELECT u.uid, u.name, u.gender, MIN(l.timestamp) AS ts
            FROM users u
            LEFT JOIN logs l ON l.uid = u.uid AND DATE(l.timestamp) = ?
            WHERE u.grade = ?
            GROUP BY u.uid
            ORDER BY u.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $date, $grade);
} else { // all
    $sql = "SELECT u.uid, u.name, u.gender, MIN(l.timestamp) AS ts
            FROM users u
            LEFT JOIN logs l ON l.uid = u.uid AND DATE(l.timestamp) = ?
            GROUP BY u.uid
            ORDER BY u.grade, u.section, u.name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $date);
}

$stmt->execute();
$result = $stmt->get_result();

// Counters
$total = 0;
$present = 0; // includes late
$absent = 0;
$late = 0;

$male = ['total'=>0,'present'=>0,'absent'=>0,'late'=>0];
$female = ['total'=>0,'present'=>0,'absent'=>0,'late'=>0];

// process rows
while ($row = $result->fetch_assoc()) {
    $total++;
    $gender = isset($row['gender']) ? strtolower($row['gender']) : 'male';
    $ts = $row['ts']; // possibly null

    if ($ts === null) {
        // absent
        $absent++;
        if ($gender === 'female') $female['absent']++; else $male['absent']++;
        if ($gender === 'female') $female['total']++; else $male['total']++;
    } else {
        // got a timestamp -> present or late
        $timeOnly = date('H:i:s', strtotime($ts));
        // compare using strtotime of the times
        $isLate = (strtotime($timeOnly) > strtotime($cutoff_input . ':00'));
        $present++;
        if ($isLate) $late++;
        if ($gender === 'female') {
            $female['total']++;
            $female['present']++;
            if ($isLate) $female['late']++;
        } else {
            $male['total']++;
            $male['present']++;
            if ($isLate) $male['late']++;
        }
    }
}

$stmt->close();

// helper for percent (avoid division by zero)
function pct($num, $den) {
    if ($den <= 0) return 0;
    return round(($num / $den) * 100, 1);
}

// prepare values for display
$present_pct = pct($present, $total);
$absent_pct = pct($absent, $total);
$late_pct = pct($late, $total);

$male_present_pct = pct($male['present'], $male['total']);
$male_absent_pct  = pct($male['absent'], $male['total']);
$male_late_pct    = pct($male['late'], $male['total']);

$female_present_pct = pct($female['present'], $female['total']);
$female_absent_pct  = pct($female['absent'], $female['total']);
$female_late_pct    = pct($female['late'], $female['total']);

// CSV export
if ($exportCsv) {
    $filename = "attendance_stats_{$scope}_{$grade}_{$section}_{$date}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    $out = fopen('php://output', 'w');
    // header rows
    fputcsv($out, ['Scope', $scope]);
    fputcsv($out, ['Grade', $grade]);
    fputcsv($out, ['Section', $section]);
    fputcsv($out, ['Date', $date]);
    fputcsv($out, ['Cutoff', $cutoff_input]);
    fputcsv($out, []);
    fputcsv($out, ['Metric','Count','Total','Percent']);
    fputcsv($out, ['Present', $present, $total, $present_pct.'%']);
    fputcsv($out, ['Absent', $absent, $total, $absent_pct.'%']);
    fputcsv($out, ['Late', $late, $total, $late_pct.'%']);
    fputcsv($out, []);
    fputcsv($out, ['Gender','Metric','Count','Total','Percent']);
    fputcsv($out, ['Male','Present',$male['present'],$male['total'],$male_present_pct.'%']);
    fputcsv($out, ['Male','Absent',$male['absent'],$male['total'],$male_absent_pct.'%']);
    fputcsv($out, ['Male','Late',$male['late'],$male['total'],$male_late_pct.'%']);
    fputcsv($out, []);
    fputcsv($out, ['Female','Present',$female['present'],$female['total'],$female_present_pct.'%']);
    fputcsv($out, ['Female','Absent',$female['absent'],$female['total'],$female_absent_pct.'%']);
    fputcsv($out, ['Female','Late',$female['late'],$female['total'],$female_late_pct.'%']);
    fclose($out);
    exit;
}

// helper for circle style with specific colors
function circleStyleColor($percent, $type='present') {
    $p = max(0, min(100, $percent));
    // choose color by type
    $color = '#28a745'; // present green
    if ($type==='absent') $color = '#dc3545';
    if ($type==='late') $color = '#ffc107';
    return "background: conic-gradient($color {$p}%, #e9ecef {$p}%);";
}

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Statistics — <?=esc($date)?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{--accent:#007bff;--green:#28a745;--red:#dc3545;--yellow:#ffc107;
      --card-bg:#fff;--page-bg:#f4f9ff}
    body{font-family:Arial, sans-serif;background:var(--page-bg);margin:0;padding:18px;color:#222}
    header{display:flex;align-items:center;justify-content:space-between;gap:12px}
    h1{margin:0;font-size:20px}
    .controls{margin:18px 0;padding:12px;background:var(--card-bg);border-radius:10px;box-shadow:0 6px 20px rgba(0,0,0,.04)}
    .row{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
    label{font-weight:600;margin-right:6px}
    select,input[type=date],button,input[type=time]{padding:8px 10px;border-radius:8px;border:1px solid #d1d7e0;background:white}
    .radio {margin-right:12px}
    .big-area{display:flex;flex-direction:column;align-items:center;gap:18px;margin-top:18px}
    .circles{display:flex;gap:30px;align-items:center;justify-content:center;flex-wrap:wrap}
    .circle {width:160px;height:160px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 18px rgba(0,0,0,.06);position:relative;background:#e9ecef}
    .circle .label {position:absolute;top:14px;font-weight:700}
    .circle .value {font-size:22px;font-weight:800}
    .circle .sub {position:absolute;bottom:12px;font-size:14px;color:#444}
    .numbers-row{display:flex;gap:30px;justify-content:center;margin-top:6px}
    .num-box{width:160px;text-align:center}
    .gender-wrap{display:flex;gap:20px;align-items:flex-start;justify-content:center;margin-top:28px;flex-wrap:wrap}
    .gender-col{width:320px;background:var(--card-bg);border-radius:10px;padding:14px;box-shadow:0 6px 18px rgba(0,0,0,.04)}
    .gender-title{font-weight:700;margin-bottom:10px;text-align:center}
    .mini-circles{display:flex;gap:12px;align-items:center;justify-content:center}
    .mini-circle{width:90px;height:90px;border-radius:50%;display:flex;align-items:center;justify-content:center;position:relative;background:#e9ecef}
    .mini-circle .v{font-weight:700}
    .legend{font-size:13px;color:#666;text-align:center;margin-top:8px}
    .actions{display:flex;gap:12px;justify-content:center;margin-top:12px}
    .btn{padding:10px 14px;border-radius:8px;border:none;background:var(--accent);color:#fff;font-weight:700;cursor:pointer}
    .btn.secondary{background:#6c757d}
    @media (max-width:900px){
      .gender-col{width:100%}
      .circle{width:120px;height:120px}
      .num-box, .circle {width:120px}
    }
  </style>
</head>
<body>
  <header>
    <h1>Statistics — <?=esc($date)?></h1>
    <div style="text-align:right"><a href="statistics.php" style="text-decoration:none;color:var(--accent)">← Back to menu</a></div>
  </header>

  <div class="controls">
    <form method="get" id="filterForm" style="display:flex;flex-direction:column;gap:12px">
      <div class="row">
        <label>Scope:</label>
        <label class="radio"><input type="radio" name="scope" value="section" <?=($scope==='section'?'checked':'')?> onchange="onScopeChange()"> Individual Section</label>
        <label class="radio"><input type="radio" name="scope" value="grade" <?=($scope==='grade'?'checked':'')?> onchange="onScopeChange()"> Whole Grade</label>
        <label class="radio"><input type="radio" name="scope" value="all" <?=($scope==='all'?'checked':'')?> onchange="onScopeChange()"> All</label>
      </div>

      <div class="row" style="align-items:center">
        <label>Grade:</label>
        <select name="grade" id="gradeSelect" onchange="fetchSectionsAndSubmit(this.value)">
          <?php for($g=7;$g<=12;$g++): ?>
            <option value="<?=$g?>" <?=($g==$grade?'selected':'')?>>Grade <?=$g?></option>
          <?php endfor; ?>
        </select>

        <label style="margin-left:8px">Section:</label>
        <select name="section" id="sectionSelect" <?=($scope!=='section'?'disabled':'')?>>
          <option value="">-- select section --</option>
          <?php foreach($sections as $sec): ?>
            <option value="<?=esc($sec)?>" <?=($sec===$section?'selected':'')?>><?=esc($sec)?></option>
          <?php endforeach; ?>
        </select>

        <label style="margin-left:8px">Date:</label>
        <input type="date" name="date" value="<?=esc($date)?>">

        <label style="margin-left:8px">Cutoff:</label>
        <input type="time" name="cutoff" value="<?=esc($cutoff_input)?>">

        <div class="actions">
          <button type="submit" class="btn">Apply</button>
          <button type="button" class="btn secondary" onclick="exportCsv()">Export CSV</button>
        </div>
      </div>
    </form>
  </div>

  <div class="big-area">
    <div class="circles" aria-hidden="false">
      <div class="circle" style="<?=circleStyleColor($present_pct,'present')?>">
        <div class="label">Present Rate</div>
        <div class="value"><?=$present_pct?>%</div>
        <div class="sub"><?=esc($present)?> / <?=esc($total)?></div>
      </div>

      <div class="circle" style="<?=circleStyleColor($absent_pct,'absent')?>">
        <div class="label">Absent Rate</div>
        <div class="value"><?=$absent_pct?>%</div>
        <div class="sub"><?=esc($absent)?> / <?=esc($total)?></div>
      </div>

      <div class="circle" style="<?=circleStyleColor($late_pct,'late')?>">
        <div class="label">Late Rate</div>
        <div class="value"><?=$late_pct?>%</div>
        <div class="sub"><?=esc($late)?> / <?=esc($total)?></div>
      </div>
    </div>

    <div class="numbers-row">
      <div class="num-box"><strong>Present</strong><br><?=esc($present)?> / <?=esc($total)?></div>
      <div class="num-box"><strong>Absent</strong><br><?=esc($absent)?> / <?=esc($total)?></div>
      <div class="num-box"><strong>Late</strong><br><?=esc($late)?> / <?=esc($total)?></div>
    </div>

    <div class="gender-wrap">
      <div class="gender-col">
        <div class="gender-title">👨 Male (<?=esc($male['total'])?>)</div>
        <div class="mini-circles">
          <div class="mini-circle" style="<?=circleStyleColor($male_present_pct,'present')?>">
            <div class="v"><?=$male_present_pct?>%</div>
            <div style="position:absolute;bottom:8px;font-size:12px"><?=esc($male['present'])?>/<?=esc($male['total'])?></div>
          </div>
          <div class="mini-circle" style="<?=circleStyleColor($male_absent_pct,'absent')?>">
            <div class="v"><?=$male_absent_pct?>%</div>
            <div style="position:absolute;bottom:8px;font-size:12px"><?=esc($male['absent'])?>/<?=esc($male['total'])?></div>
          </div>
          <div class="mini-circle" style="<?=circleStyleColor($male_late_pct,'late')?>">
            <div class="v"><?=$male_late_pct?>%</div>
            <div style="position:absolute;bottom:8px;font-size:12px"><?=esc($male['late'])?>/<?=esc($male['total'])?></div>
          </div>
        </div>
        <div class="legend">Present / Absent / Late</div>
      </div>

      <div class="gender-col">
        <div class="gender-title">👩 Female (<?=esc($female['total'])?>)</div>
        <div class="mini-circles">
          <div class="mini-circle" style="<?=circleStyleColor($female_present_pct,'present')?>">
            <div class="v"><?=$female_present_pct?>%</div>
            <div style="position:absolute;bottom:8px;font-size:12px"><?=esc($female['present'])?>/<?=esc($female['total'])?></div>
          </div>
          <div class="mini-circle" style="<?=circleStyleColor($female_absent_pct,'absent')?>">
            <div class="v"><?=$female_absent_pct?>%</div>
            <div style="position:absolute;bottom:8px;font-size:12px"><?=esc($female['absent'])?>/<?=esc($female['total'])?></div>
          </div>
          <div class="mini-circle" style="<?=circleStyleColor($female_late_pct,'late')?>">
            <div class="v"><?=$female_late_pct?>%</div>
            <div style="position:absolute;bottom:8px;font-size:12px"><?=esc($female['late'])?>/<?=esc($female['total'])?></div>
          </div>
        </div>
        <div class="legend">Present / Absent / Late</div>
      </div>
    </div>
  </div>

<script>
function onScopeChange(){
  const form = document.getElementById('filterForm');
  const scope = form.scope.value;
  const sec = document.getElementById('sectionSelect');
  if(scope === 'section') sec.disabled = false;
  else sec.disabled = true;
}

function fetchSectionsAndSubmit(g){
  const form = document.getElementById('filterForm');
  form.grade.value = g;
  form.submit();
}

function exportCsv(){
  // build URL with current params + export=1
  const params = new URLSearchParams(new FormData(document.getElementById('filterForm')));
  params.set('export','1');
  window.location = window.location.pathname + '?' + params.toString();
}

onScopeChange();
</script>
</body>
</html>
