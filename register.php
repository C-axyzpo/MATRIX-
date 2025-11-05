<?php
function esc($str) { return htmlspecialchars($str, ENT_QUOTES, 'UTF-8'); }

$conn = new mysqli("localhost","root","","attendance");
if ($conn->connect_error) { die("DB connection failed: " . $conn->connect_error); }

$grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';
if ($grade === 0 || $section=='') die("Missing grade or section.");

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $conn->real_escape_string($_POST['uid']);
    $name = $conn->real_escape_string($_POST['name']);
    $gender = $conn->real_escape_string($_POST['gender']);

    if ($uid && $name && $gender) {
        $sql = "INSERT INTO users (uid,name,gender,section,grade) 
                VALUES ('$uid','$name','$gender','$section',$grade)";
        if ($conn->query($sql)) {
            $msg = "✅ User registered successfully!";
        } else {
            $msg = "❌ Error: ".$conn->error;
        }
    } else {
        $msg = "⚠ Please fill all fields.";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register User - <?=esc($section)?></title>
  <style>
    body{font-family:Arial,sans-serif;background:#f4f9ff;margin:0;padding:0}
    header{background:#007bff;color:#fff;padding:15px;text-align:center}
    .box{max-width:500px;margin:20px auto;background:#fff;padding:20px;border-radius:10px;box-shadow:0 6px 18px rgba(0,0,0,.05)}
    label{display:block;margin:12px 0 5px;font-weight:bold}
    input,select{width:100%;padding:10px;border:1px solid #ccc;border-radius:6px}
    button{margin-top:15px;padding:10px 16px;background:#007bff;color:#fff;border:none;border-radius:6px;cursor:pointer}
    button:hover{background:#0056b3}
    .msg{margin-top:10px;font-weight:bold}
    .back{display:inline-block;margin:15px;padding:10px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none}
    .back:hover{background:#5a6268}
  </style>
</head>
<body>
  <header>
    <h1>Register New User - <?=esc($grade . " " . $section)?></h1>
  </header>

  <a href="section_dashboard.php?grade=<?=$grade?>&section=<?=urlencode($section)?>" class="back">⬅ Back</a>

  <div class="box">
    <?php if($msg): ?><div class="msg"><?=esc($msg)?></div><?php endif; ?>
    <form method="post">
      <label>UID</label>
      <input type="text" name="uid" required>

      <label>Name</label>
      <input type="text" name="name" required>

      <label>Gender</label>
      <select name="gender" required>
        <option value="">Select...</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>

      <button type="submit">Register</button>
    </form>
  </div>
</body>
</html>
