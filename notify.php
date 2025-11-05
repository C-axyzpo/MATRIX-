<?php
// notify.php - browser test endpoint
require 'send_message_helper.php';
$pageToken = 'EAAV3Q1XhQtABPhM8Re4adZBoyyueSSqCYIKN7kVLsr4zdtBjHlrvh6ku8WZBqm0RwVB5tEReMy75M1RKEAFndBzN6YfA6b5wqa5dm23TS5Jym3i7XkZASOZBdhTsOX9i7u67f1c1vGCoPA8MTpZAoen3G9W8SDj4PoSk1BHZC5eVR73vtNFZATkZBxoZBBRJjyGLIZCnbdP8YorAZDZD';
$demo_secret = 'demo_secret_abc'; // change to something secret

// simple secret check
if (!isset($_GET['secret']) || $_GET['secret'] !== $demo_secret) {
    http_response_code(403);
    die("Forbidden. Provide ?secret={$demo_secret}");
}

$uid = $_GET['uid'] ?? null;
$name = $_GET['name'] ?? null;
$section = $_GET['section'] ?? null;
$time = $_GET['time'] ?? date('Y-m-d H:i:s');

if ($uid) {
    $conn = new mysqli("localhost","root","","attendance");
    if ($conn->connect_error) die("DB error");
    $stmt = $conn->prepare("SELECT fullname, section, parent_psid FROM students WHERE uid = ? LIMIT 1");
    $stmt->bind_param("s",$uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $name = $row['fullname'];
        $section = $row['section'];
        $parentPSID = $row['parent_psid'];
    } else {
        die("Student not found");
    }
    $stmt->close();
    $conn->close();
} else {
    if (!$name || !$section) die("Provide uid or name+section");
    $parentPSID = $_GET['psid'] ?? null;
}

if (empty($parentPSID)) die("Parent PSID not set. Parent must message the Page and REGISTER.");

$displayTime = date('M d, Y h:i A', strtotime($time));
$message = "✅ Your child \"{$name}\" of \"{$section}\" has entered school at {$displayTime}.";

$res = sendMessageToPSID($parentPSID, $message, $pageToken);
header('Content-Type: application/json');
echo json_encode(['sent'=>$res, 'message'=>$message]);
