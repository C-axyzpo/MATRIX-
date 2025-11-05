<?php
// ========== AUTHENTICATION ==========
session_start();

// If not logged in, redirect to login.php
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    header("Location: login.php");
    exit;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>The Matrix - Main Menu</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: radial-gradient(circle at center, #0a0f1f, #000);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #00ffcc;
      overflow: hidden;
    }

    .container {
      text-align: center;
      animation: fadeIn 1.5s ease-in-out;
      position: relative;
      z-index: 1;
    }

    h1 {
      margin-bottom: 40px;
      font-size: 42px;
      letter-spacing: 3px;
      text-transform: uppercase;
      text-shadow: 0 0 20px #00ffcc, 0 0 40px #00ffcc;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(2, 220px);
      gap: 30px;
      justify-content: center;
    }

    .btn {
      display: flex;
      align-items: center;
      justify-content: center;
      height: 120px;
      background: #00ffcc;
      color: #000;
      font-size: 22px;
      font-weight: bold;
      border-radius: 15px;
      text-decoration: none;
      transition: all 0.3s ease;
      box-shadow: 0 0 15px #00ffcc, 0 0 30px #00ffcc;
    }

    .btn:hover {
      background: #00e6b8;
      box-shadow: 0 0 25px #00ffcc, 0 0 50px #00ffcc;
      transform: scale(1.05);
    }

    /* Back button styling */
    .back {
      display: inline-block;
      margin-top: 50px;
      padding: 12px 24px;
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

    /* Matrix falling text effect */
    .matrix-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      color: rgba(0, 255, 200, 0.15);
      font-size: 18px;
      font-family: monospace;
      overflow: hidden;
      pointer-events: none;
      z-index: 0;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="matrix-bg" id="matrix"></div>

  <div class="container">
    <h1>Main Menu</h1>
    <div class="grid">
      <a href="menu2.php" class="btn">Attendance</a>
      <a href="borrow.php" class="btn">Borrow</a>
      <a href="clinic_dashboard.php" class="btn">Clinic</a>
      <a href="wallet_dashboard.php" class="btn">Wallet</a>
    </div>

    <!-- Logout button -->
    <a href="logout.php" class="back">🚪 Logout</a>
  </div>

  <script>
    // Matrix code rain background
    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d");
    document.getElementById("matrix").appendChild(canvas);
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    const fontSize = 16;
    const columns = canvas.width / fontSize;
    const drops = Array(Math.floor(columns)).fill(1);

    function draw() {
      ctx.fillStyle = "rgba(0, 0, 0, 0.1)";
      ctx.fillRect(0, 0, canvas.width, canvas.height);

      ctx.fillStyle = "#00ffcc";
      ctx.font = fontSize + "px monospace";

      for (let i = 0; i < drops.length; i++) {
        const text = letters.charAt(Math.floor(Math.random() * letters.length));
        ctx.fillText(text, i * fontSize, drops[i] * fontSize);

        if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
          drops[i] = 0;
        }
        drops[i]++;
      }
    }

    setInterval(draw, 33);

    // Auto resize
    window.addEventListener("resize", () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
  </script>
</body>
</html>
