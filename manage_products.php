<?php
/**
 * MarketLink — Admin Product Management
 */
require_once '../config.php';
session_start();

// Security Check: Verify if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$adminName = $_SESSION['fullname'] ?? 'Admin';
$successMsg = '';
$errorMsg = '';

// Handle Product Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $productIdToDelete = intval($_GET['id']);
    try {
        $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $delStmt->execute([$productIdToDelete]);
        $successMsg = "Product deleted successfully.";
    } catch (PDOException $e) {
        $errorMsg = "Error deleting product: " . $e->getMessage();
    }
}

// Fetch All Products from Database (with Farmer info if relation exists)
try {
    // Agar aapke table ka naam ya columns mukhtalif hain toh apne database ke mutabiq adjust kar sakte hain
    $query = "SELECT p.*, u.fullname as farmer_name FROM products p LEFT JOIN users u ON p.farmer_id = u.id ORDER BY p.id DESC";
    $stmt = $pdo->query($query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fallback agar join mein koi issue ho
    try {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        $errorMsg = "Database error: " . $ex->getMessage();
        $products = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Products — MarketLink Admin</title>
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
  --danger: #a9502f;
}
*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0; background: var(--canvas); color: var(--ink);
  font-family: var(--font-body); font-size: 16px; line-height: 1.55;
  display: flex; min-height: 100vh;
}
/* Sidebar */
.sidebar { width: 260px; background: var(--evergreen-2); color: #fbf8ef; display: flex; flex-direction: column; justify-content: space-between; padding: 24px; }
.sidebar-top .logo { font-family: var(--font-display); font-size: 1.35rem; font-weight: 680; color: #fbf8ef; text-decoration: none; display: block; margin-bottom: 30px; }
.nav-links { list-style: none; padding: 0; margin: 0; }
.nav-links li { margin-bottom: 12px; }
.nav-links a { color: #d0dcce; text-decoration: none; font-weight: 500; display: block; padding: 10px 14px; border-radius: var(--radius); transition: 0.2s; }
.nav-links a:hover, .nav-links a.active { background: var(--evergreen); color: #fff; }
.logout-btn { color: #ffb8b8; text-decoration: none; font-weight: 600; display: block; padding: 10px 14px; border-radius: var(--radius); background: rgba(169, 80, 47, 0.2); text-align: center; }
.logout-btn:hover { background: var(--danger); color: #fff; }

/* Main Content Area */
.main-content { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
.topbar { background: var(--card-bg); border-bottom: 1px solid var(--cream-line); padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
.topbar h2 { margin: 0; font-family: var(--font-display); color: var(--evergreen-2); font-size: 1.5rem; }
.admin-profile { font-weight: 600; color: var(--evergreen); }

.content-body { padding: 40px; }
.alert-success { background: #e6edd9; color: var(--evergreen-2); border: 1px solid var(--evergreen); padding: 12px; border-radius: var(--radius); margin-bottom: 20px; font-size: 0.9rem; }
.alert-error { background: #fdf2f0; color: var(--danger); border: 1px solid rgba(169, 80, 47, 0.2); padding: 12px; border-radius: var(--radius); margin-bottom: 20px; font-size: 0.9rem; }

/* Data Table */
.table-container { background: var(--card-bg); border: 1px solid var(--cream-line); border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(32, 40, 28, 0.02); }
.table-container h3 { margin-top: 0; font-family: var(--font-display); color: var(--evergreen-2); margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; text-align: left; }
th, td { padding: 12px 16px; border-bottom: 1px solid var(--cream-line); font-size: 0.92rem; }
th { font-weight: 600; color: var(--evergreen-2); background: #f5f0e1; }
tr:hover { background: rgba(32, 40, 28, 0.01); }
.action-link { color: var(--danger); text-decoration: none; font-weight: 600; font-size: 0.85rem; }
.action-link:hover { text-decoration: underline; }
</style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="sidebar">
  <div class="sidebar-top">
    <a href="admin.php" class="logo">🧺 MarketLink Admin</a>
    <ul class="nav-links">
      <li><a href="admin.php">Dashboard</a></li>
      <li><a href="manage_products.php" class="active">Manage Products</a></li>
      <li><a href="../markets.php" target="_blank">View Live Site</a></li>
    </ul>
  </div>
  <div>
    <a href="../logout.php" class="logout-btn">Log Out</a>
  </div>
</div>

<!-- Main Section -->
<div class="main-content">
  <div class="topbar">
    <h2>Product Management</h2>
    <div class="admin-profile">Welcome, <?php echo htmlspecialchars($adminName); ?></div>
  </div>

  <div class="content-body">
    
    <?php if (!empty($successMsg)): ?>
      <div class="alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>
    <?php if (!empty($errorMsg)): ?>
      <div class="alert-error"><?php echo htmlspecialchars($errorMsg); ?></div>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="table-container">
      <h3>All Listed Products</h3>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Price</th>
            <th>Farmer / Seller</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($products)): ?>
            <?php foreach ($products as $p): ?>
              <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['name'] ?? $p['title'] ?? 'N/A'); ?></td>
                <td><?php echo isset($p['price']) ? 'Rs. ' . htmlspecialchars($p['price']) : 'N/A'; ?></td>
                <td><?php echo htmlspecialchars($p['farmer_name'] ?? 'Unknown Farmer'); ?></td>
                <td>
                  <a href="manage_products.php?action=delete&id=<?php echo $p['id']; ?>" class="action-link" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" style="text-align: center; color: #777;">No products found in database.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

</body>
</html>