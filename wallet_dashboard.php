<?php
// ================== DB CONNECT ==================
$conn = new mysqli("localhost", "root", "", "attendance");
if ($conn->connect_error) die("DB failed: " . $conn->connect_error);

// ================== HANDLE TOP-UP / DEDUCT ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_POST['uid'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($uid && $amount > 0) {
        // Create wallet if not exists
        $conn->query("INSERT IGNORE INTO wallet (uid, balance) VALUES ('$uid',0)");

        if ($action === 'topup') {
            $stmt = $conn->prepare("UPDATE wallet SET balance = balance + ? WHERE uid=?");
        } elseif ($action === 'deduct') {
            $stmt = $conn->prepare("UPDATE wallet SET balance = balance - ? WHERE uid=?");
        }

        if (isset($stmt)) {
            $stmt->bind_param("ds", $amount, $uid);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// ================== FETCH USERS + BALANCES ==================
$sql = "SELECT u.uid, u.name, u.section, IFNULL(w.balance,0) as balance
        FROM users u
        LEFT JOIN wallet w ON u.uid = w.uid
        ORDER BY u.name";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Wallet Dashboard</title>
    <style>
        body { font-family: Arial; background: #f5f5f5; margin: 20px; }
        table { border-collapse: collapse; width: 100%; background: white; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #333; color: white; }
        input[type=number] { width: 70px; padding: 5px; }
        button { padding: 5px 10px; margin: 2px; }
        .top-buttons { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="top-buttons">
        <a href="menu1_5.php"><button>⬅ Back to Menu</button></a>
    </div>

    <h2>💳 Student Wallet Dashboard</h2>
    <table>
        <tr>
            <th>UID</th>
            <th>Name</th>
            <th>Section</th>
            <th>Balance (₱)</th>
            <th>Top Up</th>
            <th>Deduct</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['uid'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['section'] ?></td>
                <td><?= number_format($row['balance'], 2) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="uid" value="<?= $row['uid'] ?>">
                        <input type="number" step="0.01" name="amount" placeholder="₱" required>
                        <input type="hidden" name="action" value="topup">
                        <button type="submit">Top Up</button>
                    </form>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="uid" value="<?= $row['uid'] ?>">
                        <input type="number" step="0.01" name="amount" placeholder="₱" required>
                        <input type="hidden" name="action" value="deduct">
                        <button type="submit">Deduct</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
