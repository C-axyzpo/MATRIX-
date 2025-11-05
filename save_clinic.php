<?php
// ================== SETTINGS ==================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila'); // Manila time

$PAGE_ACCESS_TOKEN = "EAAV3Q1XhQtABPsiOZAbvfmupjkmHXw85FeeuLyXQw4vQxkrbvTCUAJfXi5sEPyyyWidaaEV5YiglyIN2vCsJBuCAQiqrzI5dZA5dZCUZAeLBlUUjv4NnhheXhbHvWHNil0lTSjkZBjo0A5lkFtAqWV4GZCCoZBoFaytJrEHD5ZATvZCGNzJ4NRVDUFVzzjZBXsONiQZCVwSG2hv&"; // Messenger token

// ================== DB CONNECTION ==================
$conn = new mysqli("localhost", "root", "", "attendance");
if ($conn->connect_error) die("DB failed: " . $conn->connect_error);

// ================== GET POST/GET DATA ==================
$uid    = $_POST['uid'] ?? $_GET['uid'] ?? '';
$reason = $_POST['reason'] ?? $_GET['reason'] ?? 'Clinic Visit';

$response = [
    "name"    => "Unknown",
    "grade"   => "",
    "section" => "",
    "gender"  => "",
    "reason"  => $reason,
    "status"  => "Error"
];

if (!$uid) {
    $response["status"] = "Missing UID";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// ================== LOOKUP USER ==================
$stmt = $conn->prepare("SELECT name, section, gender, grade, parent_psid FROM users WHERE uid=? LIMIT 1");
$stmt->bind_param("s", $uid);
$stmt->execute();
$stmt->bind_result($name, $section, $gender, $grade, $parent_psid);

if ($stmt->fetch()) {
    $stmt->close();

    // ================== INSERT CLINIC LOG ==================
    $stmt2 = $conn->prepare("INSERT INTO clinic_logs (uid, name, section, gender, grade, reason) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("ssssss", $uid, $name, $section, $gender, $grade, $reason);
    $stmt2->execute();
    $stmt2->close();

    // ================== FORMAT RESPONSE ==================
    $lcdMessage = "$name\n$grade$section|$reason";

    $response = [
        "name"    => $name,
        "grade"   => $grade,
        "section" => $section,
        "gender"  => $gender,
        "reason"  => $reason,
        "status"  => "Logged",
        "lcd"     => $lcdMessage
    ];

    // ================== MESSENGER NOTIFY ==================
    if (!empty($parent_psid)) {
        $url = "https://graph.facebook.com/v21.0/me/messages?access_token=$PAGE_ACCESS_TOKEN";
        $message = "🏥 Your child $name ($grade-$section | $gender) is at the clinic at " . date("h:i A") . ". Reason: $reason.";

        $payload = [
            'recipient' => ['id' => $parent_psid],
            'message'   => ['text' => $message]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        file_put_contents("clinic_send_log.txt", date("Y-m-d H:i:s") . " - $result\n", FILE_APPEND);
    }

} else {
    $response["status"] = "UID not found";
}

$conn->close();

// ================== RETURN TO ARDUINO/BROWSER ==================
header('Content-Type: application/json');
echo json_encode($response);
