<?php
$siteName = 'MarketLink';
$role = $_GET['role'] ?? 'customer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Success — <?php echo htmlspecialchars($siteName); ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧺</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,560;0,9..144,680;1,9..144,500&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="" href="join.php">
<style>
:root {
  --ink: #20281c;
  --canvas: #f2ede0;
  --evergreen: #2e4a2c;
  --evergreen-2: #233a22;
  --harvest: #e0a52c;
  --cream-line: rgba(32, 40, 28, .14);
  --font-display: 'Fraunces', serif;
  --font-body: 'Work Sans', sans-serif;
  --radius: 8px;
  --card-bg: #fbf8ee;
}

*, *::before, *::after {
  box-sizing: border-box;
}

body {
  margin: 0;
  background: var(--canvas);
  color: var(--ink);
  font-family: var(--font-body);
  font-size: 16px;
  line-height: 1.55;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  animation: pageEntrance 0.8s ease-out forwards;
}

@keyframes pageEntrance {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

.success-card {
  width: 100%;
  max-width: 480px;
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  padding: 48px 36px;
  border-radius: 16px;
  box-shadow: 0 12px 35px rgba(32, 40, 28, 0.06);
  text-align: center;
}

.icon-box {
  width: 72px;
  height: 72px;
  background: #e3efd8;
  color: var(--evergreen);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin: 0 auto 24px auto;
}

.success-card h1 {
  font-family: var(--font-display);
  font-size: 1.85rem;
  color: var(--evergreen-2);
  margin-bottom: 12px;
}

.success-card p {
  font-size: 0.98rem;
  color: var(--ink);
  opacity: 0.8;
  margin-bottom: 32px;
  line-height: 1.6;
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 12px 20px;
  border-radius: var(--radius);
  font-weight: 600;
  font-size: 0.95rem;
  text-decoration: none;
  background: var(--evergreen);
  color: #fbf8ef;
  transition: transform 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
}

.btn:hover {
  background: var(--evergreen-2);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(46, 74, 44, 0.15);
}
</style>
</head>
<body>

<div class="success-card">
  <div class="icon-box">
    <?php echo ($role === 'farmer') ? '🌾' : '🎉'; ?>
  </div>

  <?php if ($role === 'farmer'): ?>
    <h1>Request Submitted!</h1>
    <p>Your request has been delivered to the admin. We will review your details and notify you via email within <strong>24 hours</strong>.</p>
  <?php else: ?>
    <h1>Account Created Successfully!</h1>
    <p>Welcome to MarketLink! Your customer account has been successfully created. You can now log in to explore fresh farm goods.</p>
  <?php endif; ?>

  <a href="login.php" class="btn">Proceed to Login</a>
</div>

</body>
</html>