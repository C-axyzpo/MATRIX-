<?php
$conn = new mysqli("localhost", "root", "", "attendance");
if ($conn->connect_error) die("DB failed: " . $conn->connect_error);

$uid = $_POST['uid'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);

if ($uid && $amount > 0) {
    // Create wallet if not exists
    $conn->query("INSERT IGNORE INTO wallet (uid, balance) VALUES ('$uid',0)");
    // Update balance
    $stmt = $conn->prepare("UPDATE wallet SET balance = balance + ? WHERE uid=?");
    $stmt->bind_param("ds", $amount, $uid);
    $stmt->execute();
    $stmt->close();

    echo "✅ ₱$amount added to UID $uid. <a href='wallet_dashboard.php'>Back</a>";
} else {
    echo "⚠️ Invalid request.";
}
?>
