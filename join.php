<?php
/**
 * MarketLink — Sign Up (Customer or Farmer with Database Integration)
 */
require_once 'config.php'; // Database connection file

$siteName = 'MarketLink';$year     = date('Y');

$errors  = [];$success = false;
$registeredRole = 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_submit'])) {
    $role     = trim($_POST['role'] ?? 'customer');
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password =$_POST['password'] ?? '';
    $confirm  =$_POST['confirm_password'] ?? '';
    $terms    = isset($_POST['terms']);

    // Validations
    if (!in_array($role, ['customer', 'farmer'])) {$errors[] = 'Please select a valid account type.';
    }
    if ($fullname === '') {$errors[] = 'Full name is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {$errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {$errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {$errors[] = 'Passwords do not match.';
    }
    if (!$terms) {$errors[] = 'You must accept the Terms of Service and Privacy Policy.';
    }

    if (empty($errors)) {
        try {
            // Check if email already exists in database
            $checkStmt =$pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->execute([$email]);
            
            if ($checkStmt->rowCount() > 0) {$errors[] = 'This email is already registered. Please log in instead.';
            } else {
                // Hash password securely
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Set status: Farmers need approval, customers are approved automatically
                $status = ($role === 'farmer') ? 'pending' : 'approved';

                // Insert user into database
                $insertStmt =$pdo->prepare("INSERT INTO users (fullname, email, password, role, status) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->execute([$fullname, $email,$hashedPassword, $role,$status]);

                $success = true;
                $registeredRole =$role;
            }
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' .$e->getMessage();
        }
    }
}

$currentRole =$_POST['role'] ?? 'customer';
$buttonText = ($currentRole === 'farmer') ? 'Submit for Approval' : 'Sign Up';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create an Account — <?php echo htmlspecialchars($siteName); ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧺</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,560;0,9..144,680;1,9..144,500&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --ink: #20281c;
  --canvas: #f2ede0;
  --canvas-2: #e9e1cd;
  --evergreen: #2e4a2c;
  --evergreen-2: #233a22;
  --harvest: #e0a52c;
  --harvest-dark: #b9821a;
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
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  animation: pageEntrance 0.8s ease-out forwards;
}

@keyframes pageEntrance {
  0% { opacity: 0; transform: translateY(20px); }
  100% { opacity: 1; transform: translateY(0); }
}

@keyframes slideFromLeft {
  0% { opacity: 0; transform: translateX(-40px); }
  100% { opacity: 1; transform: translateX(0); }
}

@keyframes slideFromRight {
  0% { opacity: 0; transform: translateX(40px); }
  100% { opacity: 1; transform: translateX(0); }
}

.animate-slide-left {
  animation: slideFromLeft 0.8s cubic-bezier(0.1, 1, 0.3, 1) forwards;
}

.animate-slide-right {
  animation: slideFromRight 0.8s cubic-bezier(0.1, 1, 0.3, 1) forwards;
}

.page {
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  min-height: 100vh;
  width: 100%;
}

.side {
  background: var(--evergreen-2);
  color: #fbf8ef;
  padding: 48px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: var(--font-display);
  font-weight: 680;
  font-size: 1.25rem;
  color: #fbf8ef;
  text-decoration: none;
}

.side-quote {
  max-width: 360px;
  margin: auto 0;
}

.side-quote p {
  font-family: var(--font-display);
  font-size: 1.5rem;
  line-height: 1.4;
  margin-bottom: 16px;
  color: #f5f0e1;
}

.side-quote span {
  font-size: 0.9rem;
  opacity: 0.8;
  letter-spacing: 0.02em;
  color: var(--harvest);
}

.crumbs {
  display: flex;
  gap: 8px;
}

.crumb {
  width: 24px;
  height: 4px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 2px;
}

.crumb.on {
  background: var(--harvest);
  width: 36px;
}

.form-side {
  background: var(--canvas);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 24px;
  overflow-y: auto;
}

.form-card {
  width: 100%;
  max-width: 440px;
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  padding: 40px;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(32, 40, 28, 0.04);
}

.form-card h1 {
  font-family: var(--font-display);
  font-size: 2rem;
  color: var(--evergreen-2);
  margin-bottom: 8px;
}

.form-card p {
  font-size: 0.92rem;
  color: var(--ink);
  opacity: 0.75;
  margin-bottom: 24px;
}

.back-link {
  display: inline-block;
  font-size: 0.88rem;
  color: var(--evergreen);
  font-weight: 600;
  text-decoration: none;
  margin-bottom: 16px;
}

.field {
  margin-bottom: 20px;
}

.field label {
  display: block;
  font-size: 0.88rem;
  font-weight: 600;
  color: var(--evergreen-2);
  margin-bottom: 6px;
}

.field input[type="text"],
.field input[type="email"],
.field input[type="password"] {
  width: 100%;
  padding: 12px 14px;
  background: var(--canvas);
  border: 1.5px solid var(--cream-line);
  border-radius: var(--radius);
  font-family: var(--font-body);
  font-size: 0.95rem;
  color: var(--ink);
  outline: none;
}

.field input:focus {
  border-color: var(--evergreen);
  box-shadow: 0 0 0 3px rgba(46, 74, 44, 0.12);
}

.password-wrapper {
  position: relative;
}

.password-wrapper input {
  padding-right: 44px !important;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  color: var(--evergreen-2);
  opacity: 0.6;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-password:hover {
  opacity: 1;
}

.toggle-password svg {
  width: 20px;
  height: 20px;
}

.role-switch {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  background: var(--canvas);
  padding: 4px;
  border: 1.5px solid var(--cream-line);
  border-radius: var(--radius);
}

.role-switch input[type="radio"] {
  display: none;
}

.role-switch label {
  text-align: center;
  padding: 10px;
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--ink);
  border-radius: 6px;
  cursor: pointer;
  margin-bottom: 0 !important;
}

.role-switch input[type="radio"]:checked + label {
  background: var(--evergreen);
  color: #fbf8ef;
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
  cursor: pointer;
  border: 1.5px solid transparent;
  transition: transform 0.2s ease, background 0.2s ease;
}

.btn-primary {
  background: var(--evergreen);
  color: #fbf8ef;
}

.btn-primary:hover {
  background: var(--evergreen-2);
}

.msg {
  background: #fdf2f0;
  color: #a9502f;
  border: 1px solid rgba(169, 80, 47, 0.2);
  padding: 12px 16px;
  border-radius: var(--radius);
  font-size: 0.88rem;
  margin-bottom: 20px;
}

.foot-note {
  text-align: center;
  font-size: 0.88rem;
  margin-top: 24px;
  color: var(--ink);
  opacity: 0.8;
}

.foot-note a {
  color: var(--evergreen);
  font-weight: 600;
  text-decoration: none;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(32, 40, 28, 0.6);
  backdrop-filter: blur(4px);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-card {
  width: 100%;
  max-width: 440px;
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  padding: 40px 32px;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(32, 40, 28, 0.15);
  text-align: center;
}

.modal-icon {
  width: 68px;
  height: 68px;
  background: #e3efd8;
  color: var(--evergreen);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  margin: 0 auto 20px auto;
}

.modal-card h2 {
  font-family: var(--font-display);
  font-size: 1.6rem;
  color: var(--evergreen-2);
  margin-bottom: 10px;
}

.modal-card p {
  font-size: 0.95rem;
  color: var(--ink);
  opacity: 0.8;
  margin-bottom: 28px;
}
</style>
</head>
<body>

<div class="page">
  <div class="side animate-slide-left">
    <a href="landingpage.php" class="logo">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M4 10 L12 4 L20 10 V20 H4 Z" stroke="#eae4d3" stroke-width="1.6" fill="#e0a52c"/></svg>
      <?php echo htmlspecialchars($siteName); ?>
    </a>
    <div class="side-quote">
      <p>"I stopped driving to markets that turned out to be closed. I check MarketLink first, every time."</p>
      <span>— Amara, regular customer</span>
    </div>
    <div class="crumbs">
      <div class="crumb on"></div>
      <div class="crumb"></div>
      <div class="crumb"></div>
    </div>
  </div>

  <div class="form-side animate-slide-right">
    <div class="form-card">
      <a href="login.php" class="back-link">&larr; Already have an account? Log in</a>
      
      <h1>Create Account</h1>
      <p>Join MarketLink to shop or sell fresh farm goods.</p>

      <?php if (!empty($errors)): ?>
        <div class="msg">
          Please fix the following:
          <ul style="margin: 6px 0 0 16px; padding: 0;">
            <?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="#">
        <div class="field">
          <label>Continue as</label>
          <div class="role-switch">
            <input type="radio" id="role_customer" name="role" value="customer" <?php echo ($currentRole === 'customer') ? 'checked' : ''; ?>>
            <label for="role_customer">Customer</label>

            <input type="radio" id="role_farmer" name="role" value="farmer" <?php echo ($currentRole === 'farmer') ? 'checked' : ''; ?>>
            <label for="role_farmer">Farmer</label>
          </div>
        </div>

        <div class="field">
          <label for="fullname">Full name</label>
          <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input type="password" id="password" name="password" placeholder="At least 8 characters" required>
            <button type="button" class="toggle-password" data-target="password" aria-label="Toggle password visibility">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
          </div>
        </div>

        <div class="field">
          <label for="confirm_password">Confirm password</label>
          <div class="password-wrapper">
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
            <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Toggle password visibility">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
          </div>
        </div>

        <div class="field" style="display: flex; align-items: flex-start; gap: 8px; margin: 20px 0;">
          <input type="checkbox" id="terms" name="terms" required style="margin-top: 3px; width: auto;">
          <label for="terms" style="font-size: 0.85rem; font-weight: 400; color: #5a6350; margin-bottom: 0;">
            I agree to the <a href="#" style="color: var(--evergreen); font-weight: 600;">Terms of Service</a> and <a href="#" style="color: var(--evergreen); font-weight: 600;">Privacy Policy</a>.
          </label>
        </div>

        <button type="submit" id="submit_btn" name="signup_submit" class="btn btn-primary"><?php echo htmlspecialchars($buttonText); ?></button>
      </form>

      <p class="foot-note">Already registered? <a href="login.php">Log in</a></p>
    </div>
  </div>
</div>

<!-- SUCCESS / APPROVAL POP-UP MODAL -->
<div id="successModal" class="modal-overlay" style="display: <?php echo $success ? 'flex' : 'none'; ?>;">
  <div class="modal-card">
    <div class="modal-icon">
      <?php echo ($registeredRole === 'farmer') ? '🌾' : '🎉'; ?>
    </div>
    
    <?php if ($registeredRole === 'farmer'): ?>
      <h2>Request Submitted!</h2>
      <p>Your request has been delivered to the database. We will review your details within 24 hours.</p>
    <?php else: ?>
      <h2>Account Created!</h2>
      <p>Your account has been successfully saved in the database. You can now log in to start exploring.</p>
    <?php endif; ?>

    <a href="login.php" class="btn btn-primary">Proceed to Login</a>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const submitBtn = document.getElementById('submit_btn');

    roleRadios.forEach(radio => {
      radio.addEventListener('change', function() {
        if (this.value === 'farmer') {
          submitBtn.textContent = 'Submit for Approval';
        } else {
          submitBtn.textContent = 'Sign Up';
        }
      });
    });

    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        const targetId = this.getAttribute('data-target');
        const inputField = document.getElementById(targetId);

        if (inputField.type === 'password') {
          inputField.type = 'text';
          this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
        } else {
          inputField.type = 'password';
          this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        }
      });
    });
  });
</script>

</body>
</html>