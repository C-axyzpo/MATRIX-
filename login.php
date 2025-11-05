<?php
session_start();

$cooldown_time = 500; // seconds (500 seconds = 8 minutes 20 seconds)
$max_attempts = 3;

// Initialize attempt tracking
if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}
if (!isset($_SESSION['lock_time'])) {
    $_SESSION['lock_time'] = 0;
}

// Check if user is under cooldown
$remaining_cooldown = 0;
if ($_SESSION['lock_time'] > time()) {
    $remaining_cooldown = $_SESSION['lock_time'] - time();
}

// Handle login submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && $remaining_cooldown === 0) {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    // EDIT HERE 👇 (palitan mo username/password)
    $valid_user = "admin";
    $valid_pass = "admin123";

    if ($username === $valid_user && $password === $valid_pass) {
        $_SESSION["logged_in"] = true;
        $_SESSION["attempts"] = 0; // reset attempts on success
        header("Location: menu1_5.php");
        exit;
    } else {
        $_SESSION['attempts']++;

        if ($_SESSION['attempts'] >= $max_attempts) {
            $_SESSION['lock_time'] = time() + $cooldown_time;
            $error = "🚫 Too many failed attempts. Please wait $cooldown_time seconds before trying again.";
        } else {
            $remaining = $max_attempts - $_SESSION['attempts'];
            $error = "❌ Invalid username or password. You have $remaining attempt(s) left.";
        }
    }
} elseif ($remaining_cooldown > 0) {
    $error = "⏳ Too many failed attempts. Please wait {$remaining_cooldown} seconds before trying again.";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login - RFID System</title>
  <style>
    body {
      background: radial-gradient(circle at center, #0a0f1f, #000);
      color: #00ffcc;
      font-family: Segoe UI, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: rgba(0,0,0,0.7);
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 20px #00ffcc;
      text-align: center;
      width: 300px;
    }
    input {
      display: block;
      margin: 10px auto;
      padding: 10px;
      border: none;
      border-radius: 8px;
      width: 80%;
    }
    button {
      background: #00ffcc;
      border: none;
      padding: 10px 20px;
      font-weight: bold;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 10px;
    }
    button:hover {
      background: #00e6b8;
    }
    .back {
      display: inline-block;
      margin-top: 15px;
      padding: 8px 16px;
      background: transparent;
      color: #00ffcc;
      border: 2px solid #00ffcc;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 0 10px #00ffcc;
    }
    .back:hover {
      background: #00ffcc;
      color: #000;
      transform: scale(1.05);
      box-shadow: 0 0 20px #00ffcc, 0 0 40px #00ffcc;
    }
    .error {
      color: #ff6666;
      margin-bottom: 10px;
    }
    .cooldown {
      font-size: 0.9em;
      color: #ffaa00;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>RFID System Login</h2>
    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <?php if ($remaining_cooldown === 0): ?>
      <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
      </form>
    <?php else: ?>
      <p class="cooldown">Please wait <span id="cooldown"><?php echo $remaining_cooldown; ?></span> seconds...</p>
      <script>
        let time = <?php echo $remaining_cooldown; ?>;
        const el = document.getElementById('cooldown');
        const timer = setInterval(() => {
          time--;
          el.textContent = time;
          if (time <= 0) location.reload();
        }, 1000);
      </script>
    <?php endif; ?>

    <a href="menu.php" class="back">⬅ Back</a>
  </div>
</body>
</html>
