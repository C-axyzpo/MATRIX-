<?php
// ================== SETTINGS ==================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila'); // 🇵🇭 Manila time

$PAGE_ACCESS_TOKEN = "EAAV3Q1XhQtABPsiOZAbvfmupjkmHXw85FeeuLyXQw4vQxkrbvTCUAJfXi5sEPyyyWidaaEV5YiglyIN2vCsJBuCAQiqrzI5dZA5dZCUZAeLBlUUjv4NnhheXhbHvWHNil0lTSjkZBjo0A5lkFtAqWV4GZCCoZBoFaytJrEHD5ZATvZCGNzJ4NRVDUFVzzjZBXsONiQZCVwSG2hv&"; // <-- replace with your valid token

// ================== DATABASE ==================
$conn = new mysqli("localhost", "root", "", "attendance");
if ($conn->connect_error) die("DB failed: " . $conn->connect_error);

// ================== GET DATA ==================
$uid   = $_POST['uid'] ?? $_GET['uid'] ?? '';
$price = floatval($_POST['price'] ?? $_GET['price'] ?? 0);

// Log all requests
file_put_contents("purchase_log.txt", date('Y-m-d H:i:s')." UID: $uid PRICE: $price\n", FILE_APPEND);

if (!$uid || $price <= 0) {
    echo "⚠️ Invalid request";
    exit;
}

// ================== FETCH STUDENT INFO ==================
$stmt = $conn->prepare("SELECT name, grade, section, parent_psid FROM users WHERE uid=? LIMIT 1");
$stmt->bind_param("s", $uid);
$stmt->execute();
$stmt->bind_result($name, $grade, $section, $parent_psid);
$stmt->fetch();
$stmt->close();

// ================== WALLET CHECK ==================
$res = $conn->query("SELECT balance FROM wallet WHERE uid='$uid'");
if ($res->num_rows == 0) {
    $conn->query("INSERT INTO wallet (uid, balance) VALUES ('$uid', 0)");
    $balance = 0;
} else {
    $balance = floatval($res->fetch_assoc()['balance']);
}

// ================== VERIFY AND PROCESS PURCHASE ==================
if ($balance >= $price) {
    // Deduct from wallet
    $stmt = $conn->prepare("UPDATE wallet SET balance = balance - ?, last_updated = NOW() WHERE uid=?");
    $stmt->bind_param("ds", $price, $uid);
    $stmt->execute();
    $stmt->close();

    // Insert purchase record
    $stmt2 = $conn->prepare("INSERT INTO purchase (uid, item_price, timestamp) VALUES (?, ?, NOW())");
    $stmt2->bind_param("sd", $uid, $price);
    $stmt2->execute();
    $stmt2->close();

    // New remaining balance
    $newBalance = $balance - $price;

    // LCD message for Arduino
    echo "✅ Purchase OK\nRemaining: ₱" . number_format($newBalance, 2);

    // ================== NOTIFY PARENT VIA MESSENGER ==================
    if (!empty($parent_psid)) {
        $msg = "🛒 PURCHASE ALERT\n"
             . "$name ($grade-$section) made a purchase worth ₱" . number_format($price, 2) . "\n"
             . "Remaining balance: ₱" . number_format($newBalance, 2) . "\n"
             . "Time: " . date("M d, Y h:i A");

        sendMessageToPSID($parent_psid, $msg, $PAGE_ACCESS_TOKEN);
    }

} else {
    echo "❌ Insufficient funds\nBalance: ₱" . number_format($balance, 2);

    // Notify parent if failed attempt
    if (!empty($parent_psid)) {
        $msg = "⚠️ FAILED PURCHASE ALERT\n"
             . "$name ($grade-$section) attempted a ₱" . number_format($price, 2) . " purchase\n"
             . "but only has ₱" . number_format($balance, 2) . " left.";
        sendMessageToPSID($parent_psid, $msg, $PAGE_ACCESS_TOKEN);
    }
}

$conn->close();

// ================== MESSENGER FUNCTION ==================
function sendMessageToPSID($psid, $message, $PAGE_ACCESS_TOKEN) {
    $url = "https://graph.facebook.com/v21.0/me/messages?access_token=$PAGE_ACCESS_TOKEN";

    $payload = [
        'recipient' => ['id' => $psid],
        'message'   => ['text' => $message]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    file_put_contents("purchase_notify_log.txt", date("Y-m-d H:i:s") . " - $result\n", FILE_APPEND);
}
?>
