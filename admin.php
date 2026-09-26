<?php
/**
 * MarketLink — All-in-One Admin Dashboard (Single File)
 */
require_once 'config.php'; 
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$adminName = $_SESSION['fullname'] ?? 'Admin';
$successMsg = '';
$errorMsg = '';
$tab = $_GET['tab'] ?? 'dashboard';
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// --- ACTIONS HANDLING ---
if (isset($_GET['action'])) {
    try {
        if ($action === 'delete_user' && $id > 0) {
            if ($id === intval($_SESSION['user_id'])) {
                $errorMsg = "Aap apne admin account ko delete nahi kar sakte!";
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                $successMsg = "User successfully delete ho gaya.";
            }
        } elseif ($action === 'delete_product' && $id > 0) {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $successMsg = "Product successfully delete ho gaya.";
        } elseif ($action === 'delete_order' && $id > 0) {
            $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $stmt->execute([$id]);
            $successMsg = "Order successfully delete ho gaya.";
        } elseif ($action === 'accept_farmer' && $id > 0) {
            // Farmer status ko active/approved karna (Agar database mein 'status' ya 'approval' column hai)
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ? AND role = 'farmer'");
            $stmt->execute([$id]);
            $successMsg = "Farmer ki request accept ho gayi hai.";
        } elseif ($action === 'reject_farmer' && $id > 0) {
            // Farmer status ko rejected karna
            $stmt = $pdo->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role = 'farmer'");
            $stmt->execute([$id]);
            $successMsg = "Farmer ki request reject kar di gayi hai.";
        }
    } catch (PDOException $e) {
        $errorMsg = "Database error: " . $e->getMessage();
    }
}

// Handle Order Status Update
if (isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $newStatus = trim($_POST['status']);
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $orderId]);
        $successMsg = "Order status update ho gaya.";
    } catch (PDOException $e) {
        $errorMsg = "Error: " . $e->getMessage();
    }
}

// Handle Edit User Form Submission
if (isset($_POST['update_user_details'])) {
    $editId = intval($_POST['user_id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $status = trim($_POST['status'] ?? 'active');

    try {
        $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ?, role = ?, status = ? WHERE id = ?");
        $stmt->execute([$fullname, $email, $role, $status, $editId]);
        $successMsg = "User details successfully update ho gayi hain.";
        // Refresh target tab
        $tab = ($role === 'farmer') ? 'farmers' : 'users';
    } catch (PDOException $e) {
        $errorMsg = "Error updating user: " . $e->getMessage();
    }
}

// --- FETCH DATA FOR VIEWS ---
try {
    $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalFarmers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'farmer'")->fetchColumn();
    $totalCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
    
    $customers = $pdo->query("SELECT * FROM users WHERE role = 'customer' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $farmers = $pdo->query("SELECT * FROM users WHERE role = 'farmer' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    
    // Products fetch
    try {
        $products = $pdo->query("SELECT p.*, u.fullname as farmer_name FROM products p LEFT JOIN users u ON p.farmer_id = u.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $products = [];
    }

    // Orders fetch
    try {
        $orders = $pdo->query("SELECT o.*, u.fullname as customer_name FROM orders o LEFT JOIN users u ON o.customer_id = u.id ORDER BY o.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $orders = [];
    }

} catch (PDOException $e) {
    $errorMsg = "Database connection error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard — MarketLink</title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧺</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,560;0,9..144,680;1,9..144,500&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
  --ink: #20281c; --canvas: #f2ede0; --evergreen: #2e4a2c; --evergreen-2: #233a22;
  --harvest: #e0a52c; --cream-line: rgba(32, 40, 28, .14); --font-display: 'Fraunces', serif;
  --font-body: 'Work Sans', sans-serif; --radius: 8px; --card-bg: #fbf8ee; --danger: #a9502f;
}
*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; background: var(--canvas); color: var(--ink); font-family: var(--font-body); font-size: 16px; display: flex; min-height: 100vh; }
.sidebar { width: 260px; background: var(--evergreen-2); color: #fbf8ef; display: flex; flex-direction: column; justify-content: space-between; padding: 24px; }
.sidebar-top .logo { font-family: var(--font-display); font-size: 1.35rem; font-weight: 680; color: #fbf8ef; text-decoration: none; display: block; margin-bottom: 30px; }
.nav-links { list-style: none; padding: 0; margin: 0; }
.nav-links li { margin-bottom: 12px; }
.nav-links a { color: #d0dcce; text-decoration: none; font-weight: 500; display: block; padding: 10px 14px; border-radius: var(--radius); transition: 0.2s; }
.nav-links a:hover, .nav-links a.active { background: var(--evergreen); color: #fff; }
.logout-btn { color: #ffb8b8; text-decoration: none; font-weight: 600; display: block; padding: 10px 14px; border-radius: var(--radius); background: rgba(169, 80, 47, 0.2); text-align: center; }
.logout-btn:hover { background: var(--danger); color: #fff; }
.main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
.topbar { background: var(--card-bg); border-bottom: 1px solid var(--cream-line); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
.topbar h2 { margin: 0; font-family: var(--font-display); color: var(--evergreen-2); font-size: 1.5rem; }
.admin-profile { font-weight: 600; color: var(--evergreen); }
.content-body { padding: 40px; }
.alert-success { background: #e6edd9; color: var(--evergreen-2); border: 1px solid var(--evergreen); padding: 12px; border-radius: var(--radius); margin-bottom: 20px; font-size: 0.9rem; }
.alert-error { background: #fdf2f0; color: var(--danger); border: 1px solid rgba(169, 80, 47, 0.2); padding: 12px; border-radius: var(--radius); margin-bottom: 20px; font-size: 0.9rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
.stat-card { background: var(--card-bg); border: 1px solid var(--cream-line); padding: 24px; border-radius: 12px; }
.stat-card h3 { margin: 0 0 10px 0; font-size: 0.9rem; color: #556050; text-transform: uppercase; }
.stat-card .number { font-family: var(--font-display); font-size: 2.2rem; font-weight: 680; color: var(--evergreen-2); margin: 0; }
.table-container { background: var(--card-bg); border: 1px solid var(--cream-line); border-radius: 12px; padding: 24px; }
.table-container h3 { margin-top: 0; font-family: var(--font-display); color: var(--evergreen-2); margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; text-align: left; }
th, td { padding: 12px 16px; border-bottom: 1px solid var(--cream-line); font-size: 0.92rem; }
th { font-weight: 600; color: var(--evergreen-2); background: #f5f0e1; }
tr:hover { background: rgba(32, 40, 28, 0.01); }
.action-link { color: var(--evergreen); text-decoration: none; font-weight: 600; font-size: 0.85rem; margin-right: 8px; }
.action-link:hover { text-decoration: underline; }
.action-link.delete { color: var(--danger); }
.status-select, .form-control { padding: 6px 10px; border-radius: 4px; border: 1px solid var(--cream-line); background: var(--canvas); font-size: 0.85rem; width: 100%; margin-bottom: 15px; }
.update-btn { padding: 8px 16px; background: var(--evergreen); color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
.badge-pending { background: #fdf2f0; color: var(--danger); }
.badge-active { background: #e6edd9; color: var(--evergreen); }
</style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
  <div class="sidebar-top">
    <a href="admin.php?tab=dashboard" class="logo">🧺 MarketLink Admin</a>
    <ul class="nav-links">
      <li><a href="admin.php?tab=dashboard" class="<?php echo ($tab === 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
      <li><a href="admin.php?tab=users" class="<?php echo ($tab === 'users' && $action !== 'edit_user') ? 'active' : ''; ?>">Manage Customers</a></li>
      <li><a href="admin.php?tab=farmers" class="<?php echo ($tab === 'farmers' && $action !== 'edit_user') ? 'active' : ''; ?>">Manage Farmers</a></li>
      <li><a href="admin.php?tab=products" class="<?php echo ($tab === 'products') ? 'active' : ''; ?>">Manage Products</a></li>
      <li><a href="admin.php?tab=orders" class="<?php echo ($tab === 'orders') ? 'active' : ''; ?>">Manage Orders</a></li>
      <li><a href="../markets.php" target="_blank">View Live Site &rarr;</a></li>
    </ul>
  </div>
  <div>
    <a href="../logout.php" class="logout-btn">Log Out</a>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="topbar">
    <h2><?php echo ($action === 'edit_user') ? 'Edit User Details' : ucfirst($tab) . ' Management'; ?></h2>
    <div class="admin-profile">Welcome, <?php echo htmlspecialchars($adminName); ?></div>
  </div>

  <div class="content-body">
    <?php if (!empty($successMsg)): ?><div class="alert-success"><?php echo htmlspecialchars($successMsg); ?></div><?php endif; ?>
    <?php if (!empty($errorMsg)): ?><div class="alert-error"><?php echo htmlspecialchars($errorMsg); ?></div><?php endif; ?>

    <?php if ($action === 'edit_user' && $id > 0): 
        // Fetch user to edit
        $editStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $editStmt->execute([$id]);
        $editUser = $editStmt->fetch(PDO::FETCH_ASSOC);
        if ($editUser):
    ?>
      <!-- EDIT USER FORM -->
      <div class="table-container" style="max-width: 600px; margin: 0 auto;">
        <h3>Edit User: <?php echo htmlspecialchars($editUser['fullname']); ?></h3>
        <form method="POST" action="admin.php?tab=<?php echo ($editUser['role'] === 'farmer') ? 'farmers' : 'users'; ?>">
          <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
          
          <label>Full Name</label>
          <input type="text" name="fullname" class="form-control" value="<?php echo htmlspecialchars($editUser['fullname']); ?>" required>

          <label>Email Address</label>
          <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>

          <label>Role</label>
          <select name="role" class="form-control">
            <option value="customer" <?php echo ($editUser['role'] === 'customer') ? 'selected' : ''; ?>>Customer</option>
            <option value="farmer" <?php echo ($editUser['role'] === 'farmer') ? 'selected' : ''; ?>>Farmer</option>
            <option value="admin" <?php echo ($editUser['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
          </select>

          <label>Status</label>
          <select name="status" class="form-control">
            <option value="active" <?php echo (($editUser['status'] ?? 'active') === 'active') ? 'selected' : ''; ?>>Active / Approved</option>
            <option value="pending" <?php echo (($editUser['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
            <option value="rejected" <?php echo (($editUser['status'] ?? '') === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
          </select>

          <button type="submit" name="update_user_details" class="update-btn">Save Changes</button>
          <a href="admin.php?tab=<?php echo ($editUser['role'] === 'farmer') ? 'farmers' : 'users'; ?>" style="margin-left: 10px; color: var(--ink);">Cancel</a>
        </form>
      </div>
    <?php endif; ?>

    <?php elseif ($tab === 'dashboard'): ?>
      <!-- DASHBOARD OVERVIEW -->
      <div class="stats-grid">
        <div class="stat-card"><h3>Total Users</h3><p class="number"><?php echo $totalUsers; ?></p></div>
        <div class="stat-card"><h3>Total Farmers</h3><p class="number"><?php echo $totalFarmers; ?></p></div>
        <div class="stat-card"><h3>Total Customers</h3><p class="number"><?php echo $totalCustomers; ?></p></div>
      </div>
      <div class="table-container">
        <h3>Quick Summary</h3>
        <p>WELCOME TO ADMIN PANEL</p>
      </div>

    <?php elseif ($tab === 'users'): ?>
      <!-- MANAGE CUSTOMERS -->
      <div class="table-container">
        <h3>Registered Customers</h3>
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (!empty($customers)): foreach ($customers as $c): ?>
              <tr>
                <td><?php echo $c['id']; ?></td>
                <td><?php echo htmlspecialchars($c['fullname']); ?></td>
                <td><?php echo htmlspecialchars($c['email']); ?></td>
                <td>
                  <a href="admin.php?action=edit_user&id=<?php echo $c['id']; ?>" class="action-link">Edit</a>
                  <a href="admin.php?tab=users&action=delete_user&id=<?php echo $c['id']; ?>" class="action-link delete" onclick="return confirm('Delete karein?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="4" style="text-align: center;">Koi customer nahi mila.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($tab === 'farmers'): ?>
      <!-- MANAGE FARMERS -->
      <div class="table-container">
        <h3>Registered Farmers & Pending Requests</h3>
        <table>
          <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (!empty($farmers)): foreach ($farmers as $f): 
                $fStatus = $f['status'] ?? 'active';
            ?>
              <tr>
                <td><?php echo $f['id']; ?></td>
                <td><?php echo htmlspecialchars($f['fullname']); ?></td>
                <td><?php echo htmlspecialchars($f['email']); ?></td>
                <td>
                  <span class="badge <?php echo ($fStatus === 'pending') ? 'badge-pending' : 'badge-active'; ?>">
                    <?php echo ucfirst($fStatus); ?>
                  </span>
                </td>
                <td>
                  <?php if ($fStatus === 'pending'): ?>
                    <a href="admin.php?tab=farmers&action=accept_farmer&id=<?php echo $f['id']; ?>" class="action-link" style="color: #2e4a2c;">Accept</a>
                    <a href="admin.php?tab=farmers&action=reject_farmer&id=<?php echo $f['id']; ?>" class="action-link" style="color: #a9502f;">Reject</a>
                  <?php endif; ?>
                  <a href="admin.php?action=edit_user&id=<?php echo $f['id']; ?>" class="action-link">Edit</a>
                  <a href="admin.php?tab=farmers&action=delete_user&id=<?php echo $f['id']; ?>" class="action-link delete" onclick="return confirm('Delete karein?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="5" style="text-align: center;">no farmer founded</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($tab === 'products'): ?>
      <!-- MANAGE PRODUCTS -->
      <div class="table-container">
        <h3>All Products</h3>
        <table>
          <thead><tr><th>ID</th><th>Product Name</th><th>Price</th><th>Farmer</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (!empty($products)): foreach ($products as $p): ?>
              <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['name'] ?? 'N/A'); ?></td>
                <td>Rs. <?php echo htmlspecialchars($p['price'] ?? '0'); ?></td>
                <td><?php echo htmlspecialchars($p['farmer_name'] ?? 'N/A'); ?></td>
                <td><a href="admin.php?tab=products&action=delete_product&id=<?php echo $p['id']; ?>" class="action-link delete" onclick="return confirm('you sure?');">Delete</a></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="5" style="text-align: center;">Koi product nahi mili.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

    <?php elseif ($tab === 'orders'): ?>
      <!-- MANAGE ORDERS -->
      <div class="table-container">
        <h3>Customer Orders</h3>
        <table>
          <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
            <?php if (!empty($orders)): foreach ($orders as $o): ?>
              <tr>
                <td>#<?php echo $o['id']; ?></td>
                <td><?php echo htmlspecialchars($o['customer_name'] ?? 'N/A'); ?></td>
                <td>Rs. <?php echo htmlspecialchars($o['total_amount'] ?? $o['total'] ?? '0'); ?></td>
                <td>
                  <form method="post" action="admin.php?tab=orders" style="display:inline-flex; gap:6px;">
                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                    <select name="status" class="status-select">
                      <option value="pending" <?php echo (($o['status'] ?? '') === 'pending') ? 'selected' : ''; ?>>Pending</option>
                      <option value="completed" <?php echo (($o['status'] ?? '') === 'completed') ? 'selected' : ''; ?>>Completed</option>
                      <option value="cancelled" <?php echo (($o['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status" class="update-btn" style="padding: 6px 10px;">Save</button>
                  </form>
                </td>
                <td><a href="admin.php?tab=orders&action=delete_order&id=<?php echo $o['id']; ?>" class="action-link delete" onclick="return confirm('Delete karein?');">Delete</a></td>
              </tr>
            <?php endforeach; else: ?>
              <tr><td colspan="5" style="text-align: center;">Koi order nahi mila.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

  </div>
</div>

</body>
</html>