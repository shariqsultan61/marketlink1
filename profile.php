<?php
// Session aur security headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");



session_start();
require_once 'config.php';

// Agar user login nahi hai, toh login page par bhej dein
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$siteName = 'MarketLink';
$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// --- Handle Profile & Picture Update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $newFullname = trim($_POST['fullname'] ?? '');
    $newEmail = trim($_POST['email'] ?? '');

    if ($newFullname === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid name and email address.";
    } else {
        try {
            // Check karein ke email kisi aur ke paas toh nahi hai
            $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $checkStmt->execute([$newEmail, $userId]);
            
            if ($checkStmt->rowCount() > 0) {
                $error = "This email is already registered by another account.";
            } else {
                // Profile Picture Upload Handling
                $profilePicQueryPart = "";
                $params = [$newFullname, $newEmail];

                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
                    $fileName = $_FILES['profile_pic']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
                        $uploadFileDir = 'uploads/';
                        
                        // Agar uploads folder mojood nahi hai toh create kar dein
                        if (!is_dir($uploadFileDir)) {
                            mkdir($uploadFileDir, 0755, true);
                        }
                        
                        $dest_path = $uploadFileDir . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $profilePicQueryPart = ", profile_pic = ?";
                            $params[] = $newFileName;
                        } else {
                            $error = "Error moving the uploaded file.";
                        }
                    } else {
                        $error = "Allowed image formats: JPG, JPEG, PNG, WEBP.";
                    }
                }

                if (!$error) {
                    $params[] = $userId;
                    $updateStmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ? $profilePicQueryPart WHERE id = ?");
                    $updateStmt->execute($params);

                    $_SESSION['fullname'] = $newFullname;
                    $message = "Profile updated successfully!";
                }
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// --- Fetch Latest Real-time Data from Database ---
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        session_destroy();
        header("Location: login.php");
        exit;
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Profile — <?php echo htmlspecialchars($siteName); ?></title>
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
  --clay: #a9502f;
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
  flex-direction: column;
}

.site-header {
  background: var(--canvas);
  border-bottom: 1px solid var(--cream-line);
  padding: 0 24px;
}

.nav {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 72px;
}

.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  font-family: var(--font-display);
  font-weight: 680;
  font-size: 1.25rem;
  color: var(--evergreen-2);
  text-decoration: none;
}

.profile-container {
  max-width: 700px;
  width: 100%;
  margin: 40px auto;
  padding: 0 24px;
}

.profile-card {
  background: var(--card-bg);
  border: 1px solid var(--cream-line);
  border-radius: 12px;
  padding: 40px;
  box-shadow: 0 10px 30px rgba(32, 40, 28, 0.04);
}

.profile-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 30px;
  border-bottom: 1px solid var(--cream-line);
  padding-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.profile-user-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.profile-avatar {
  width: 75px;
  height: 75px;
  background: var(--evergreen);
  color: #fff;
  font-size: 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  overflow: hidden;
  border: 2px solid var(--evergreen);
}

.profile-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.profile-header h1 {
  font-family: var(--font-display);
  font-size: 1.8rem;
  color: var(--evergreen-2);
  margin: 0 0 4px 0;
}

.profile-header p {
  color: var(--ink);
  opacity: 0.7;
  font-size: 0.95rem;
  margin: 0;
  text-transform: capitalize;
}

.field {
  margin-bottom: 20px;
}

.field label {
  display: block;
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--evergreen);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 6px;
}

.field input[type="text"],
.field input[type="email"],
.field input[type="file"] {
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

.field input:disabled {
  background: rgba(0,0,0,0.03);
  cursor: not-allowed;
}

.info-value-static {
  background: var(--canvas);
  border: 1.5px solid var(--cream-line);
  padding: 12px 14px;
  border-radius: var(--radius);
  font-size: 0.95rem;
  color: var(--ink);
  opacity: 0.8;
}

.badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-pending {
  background: #fdf2f0;
  color: var(--clay);
  border: 1px solid rgba(169, 80, 47, 0.2);
}

.badge-approved {
  background: #e3efd8;
  color: var(--evergreen);
  border: 1px solid rgba(46, 74, 44, 0.2);
}

.msg-success {
  background: #e3efd8;
  color: #2e4a2c;
  padding: 12px;
  border-radius: var(--radius);
  margin-bottom: 20px;
  font-size: 0.9rem;
}

.msg-error {
  background: #fdf2f0;
  color: var(--clay);
  padding: 12px;
  border-radius: var(--radius);
  margin-bottom: 20px;
  font-size: 0.9rem;
}

.actions {
  display: flex;
  gap: 12px;
  margin-top: 30px;
  border-top: 1px solid var(--cream-line);
  padding-top: 20px;
}

.btn {
  padding: 11px 20px;
  border-radius: var(--radius);
  font-weight: 600;
  font-size: 0.95rem;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: 1.5px solid transparent;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--evergreen);
  color: #fbf8ef;
}

.btn-primary:hover {
  background: var(--evergreen-2);
}

.btn-outline {
  background: transparent;
  border-color: var(--cream-line);
  color: var(--ink);
}

.btn-outline:hover {
  border-color: var(--evergreen);
  background: var(--cream-line);
}

.hidden {
  display: none !important;
}
</style>
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a href="landingpage.php" class="logo">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M4 10 L12 4 L20 10 V20 H4 Z" stroke="#2e4a2c" stroke-width="1.6" fill="#e0a52c"/></svg>
      <?php echo htmlspecialchars($siteName); ?>
    </a>
    <a href="landingpage.php" class="btn btn-outline" style="padding: 6px 14px; font-size: 0.85rem;">← Back to Home</a>
  </nav>
</header>

<main class="profile-container">
  <div class="profile-card">

    <div class="profile-header">
        <div class="profile-user-info">
            <div class="profile-avatar">
                <?php if (!empty($userData['profile_pic']) && file_exists('uploads/' . $userData['profile_pic'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($userData['profile_pic']); ?>" alt="Profile Picture">
                <?php else: ?>
                    👤
                <?php endif; ?>
            </div>
            <div>
                <h1><?php echo htmlspecialchars($userData['fullname']); ?></h1>
                <p>Role: <strong><?php echo htmlspecialchars($userData['role']); ?></strong></p>
            </div>
        </div>
        <!-- Edit Button -->
        <button type="button" id="editToggleBtn" class="btn btn-outline" onclick="toggleEditMode()">✏️ Edit Profile</button>
    </div>

    <?php if ($message): ?>
        <div class="msg-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="msg-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Profile Form -->
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="field">
            <label for="fullname">Full Name</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($userData['fullname']); ?>" disabled required>
        </div>

        <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" disabled required>
        </div>

        <!-- Profile Picture Change Field (Initially Hidden) -->
        <div class="field hidden" id="picField">
            <label for="profile_pic">Change Profile Picture</label>
            <input type="file" id="profile_pic" name="profile_pic" accept="image/png, image/jpeg, image/jpg, image/webp">
        </div>

        <!-- Non-editable / Real-time status fields -->
        <div class="field">
            <label>Account Role <span style="font-size:0.75rem; color:#888; font-weight:normal;">(Managed by Admin)</span></label>
            <div class="info-value-static" style="text-transform: capitalize;"><?php echo htmlspecialchars($userData['role']); ?></div>
        </div>

        <div class="field">
            <label>Account / Package Status <span style="font-size:0.75rem; color:#888; font-weight:normal;">(Real-time Status)</span></label>
            <div class="info-value-static">
                <?php 
                $status = $userData['status'] ?? 'Active';
                $badgeClass = ($status === 'pending') ? 'badge-pending' : 'badge-approved';
                ?>
                <span class="badge <?php echo $badgeClass; ?>">
                    <?php echo htmlspecialchars(ucfirst($status)); ?>
                </span>
            </div>
        </div>

        <!-- Save Button (Initially Hidden) -->
        <div class="actions hidden" id="saveActions">
            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
            <button type="button" class="btn btn-outline" onclick="toggleEditMode()">Cancel</button>
        </div>
    </form>

  </div>
</main>

<script>
function toggleEditMode() {
    const nameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    const picField = document.getElementById('picField');
    const saveActions = document.getElementById('saveActions');
    const editBtn = document.getElementById('editToggleBtn');

    const isDisabled = nameInput.disabled;
    
    nameInput.disabled = !isDisabled;
    emailInput.disabled = !isDisabled;

    picField.classList.toggle('hidden');
    saveActions.classList.toggle('hidden');
    editBtn.classList.toggle('hidden');
}
</script>

</body>
</html>