<?php
/**
 * MarketLink — Log In (Admin, Farmer, Customer Redirection & Approval Popup)
 */
require_once 'config.php';
session_start();

$siteName = 'MarketLink';
$year     = date('Y');

$errors   = [];
$showApprovalPopup = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        try {
            // Database se user fetch karna
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Password verify karna
            if ($user && password_verify($password, $user['password'])) {
                
                // --- ROLE-BASED REDIRECTION CONDITION ---
                if ($user['role'] === 'admin') {
                    // Admin ke liye special session flags aur redirection to admin/admin.php
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['email']    = $user['email'];
                    $_SESSION['role']     = $user['role'];
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_email']     = $user['email'];
                    $_SESSION['admin_name']      = $user['fullname'];

                    header("Location: admin.php");
                    exit();
                } 
                elseif ($user['role'] === 'farmer') {
                    // Farmer ka status check karna ke approved hai ya nahi
                    $status = trim($user['status'] ?? 'pending');
                    if ($status !== 'approved') {
                        $showApprovalPopup = true;
                    } else {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['fullname'] = $user['fullname'];
                        $_SESSION['email']    = $user['email'];
                        $_SESSION['role']     = $user['role'];

                        header("Location: farmer_dashboard.php");
                        exit();
                    }
                } 
                elseif ($user['role'] === 'customer') {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['email']    = $user['email'];
                    $_SESSION['role']     = $user['role'];

                    header("Location: markets.php");
                    exit();
                } 
                else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['email']    = $user['email'];
                    $_SESSION['role']     = $user['role'];

                    header("Location: markets.php");
                    exit();
                }

            } else {
                $errors[] = 'Invalid email or password. Please try again.';
            }

        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Log In — <?php echo htmlspecialchars($siteName); ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧺</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,560;0,9..144,680;1,9..144,500&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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
*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0; background: var(--canvas); color: var(--ink);
  font-family: var(--font-body); font-size: 16px; line-height: 1.55;
  min-height: 100vh; display: flex; flex-direction: column;
}
.page { display: grid; grid-template-columns: 1fr 1.1fr; min-height: 100vh; width: 100%; }
.side { background: var(--evergreen-2); color: #fbf8ef; padding: 48px; display: flex; flex-direction: column; justify-content: space-between; }
.logo { display: flex; align-items: center; gap: 10px; font-family: var(--font-display); font-weight: 680; font-size: 1.25rem; color: #fbf8ef; text-decoration: none; }
.form-side { background: var(--canvas); display: flex; align-items: center; justify-content: center; padding: 40px 24px; }
.form-card { width: 100%; max-width: 440px; background: var(--card-bg); border: 1.5px solid var(--cream-line); padding: 40px; border-radius: 12px; box-shadow: 0 10px 30px rgba(32, 40, 28, 0.04); }
.form-card h1 { font-family: var(--font-display); font-size: 2rem; color: var(--evergreen-2); margin-bottom: 8px; }
.field { margin-bottom: 20px; }
.field label { display: block; font-size: 0.88rem; font-weight: 600; color: var(--evergreen-2); margin-bottom: 6px; }
.field input { width: 100%; padding: 12px 14px; background: var(--canvas); border: 1.5px solid var(--cream-line); border-radius: var(--radius); font-size: 0.95rem; outline: none; }
.btn { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 12px 20px; border-radius: var(--radius); font-weight: 600; background: var(--evergreen); color: #fbf8ef; border: none; cursor: pointer; }
.btn:hover { background: var(--evergreen-2); }
.msg { background: #fdf2f0; color: #a9502f; border: 1px solid rgba(169, 80, 47, 0.2); padding: 12px; border-radius: var(--radius); font-size: 0.88rem; margin-bottom: 20px; }
.auth-links { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; font-size: 0.88rem; }
.auth-links a { color: var(--evergreen); text-decoration: none; font-weight: 600; }
.auth-links a:hover { text-decoration: underline; }

/* Popup Modal Styling for Unapproved Farmer */
.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(32, 40, 28, 0.6); display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal-box {
  background: var(--card-bg); border: 1px solid var(--cream-line); padding: 32px; border-radius: 12px;
  max-width: 400px; width: 90%; text-align: center; box-shadow: 0 15px 35px rgba(32, 40, 28, 0.15);
}
.modal-box h3 { font-family: var(--font-display); color: var(--evergreen-2); margin-top: 0; font-size: 1.4rem; }
.modal-box p { color: var(--ink); font-size: 0.95rem; margin-bottom: 24px; }
.modal-btn { background: var(--evergreen); color: #fff; border: none; padding: 10px 24px; border-radius: var(--radius); font-weight: 600; cursor: pointer; }
.modal-btn:hover { background: var(--evergreen-2); }
</style>
</head>
<body>

<?php if ($showApprovalPopup): ?>
<div class="modal-overlay" id="approvalModal">
  <div class="modal-box">
    <h3>Notice</h3>
    <p>Your request has been submitted. Your request will be approved soon.</p>
    <button type="button" class="modal-btn" onclick="closeModal()">Okay</button>
  </div>
</div>
<script>
function closeModal() {
  document.getElementById('approvalModal').style.display = 'none';
}
</script>
<?php endif; ?>

<div class="page">
  <div class="side">
    <a href="landingpage.php" class="logo">MarketLink</a>
    <div>
      <p style="font-family: var(--font-display); font-size: 1.5rem; color: #f5f0e1;">"MarketLink ke sath apni fresh shopping ko asaan banayein."</p>
    </div>
    <div></div>
  </div>

  <div class="form-side">
    <div class="form-card">
      <a href="landingpage.php" style="font-size: 0.88rem; color: var(--evergreen); font-weight: 600; text-decoration: none;">&larr; Back to Home</a>
      
      <h1 style="margin-top: 14px;">Log In</h1>
      <p>Log in to access your MarketLink account.</p>

      <?php if (!empty($errors)): ?>
        <div class="msg">
          <ul style="margin: 0; padding-left: 16px;">
            <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="field">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
            <label for="password" style="margin-bottom: 0;">Password</label>
            <a href="forgot-password.php" style="font-size: 0.8rem; color: var(--evergreen); text-decoration: none; font-weight: 600;">Forgot Password?</a>
          </div>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" name="login_submit" class="btn">Log In</button>
      </form>

      <div class="auth-links">
        <span>Don't have an account? <a href="join.php">Sign up</a></span>
      </div>

    </div>
  </div>
</div>

</body>
</html>