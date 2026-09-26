<!-- Admin Sidebar Navigation -->
<div class="sidebar">
  <div class="sidebar-top">
    <a href="admin.php" class="logo">🧺 MarketLink Admin</a>
    <ul class="nav-links">
      <!-- Dashboard -->
      <li><a href="admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>">Dashboard</a></li>
      
      <!-- Users Management (Agar aapne alag page banaya ho) -->
      <li><a href="manage_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_users.php') ? 'active' : ''; ?>">Manage Users</a></li>
      
      <!-- Products / Market Management -->
      <li><a href="manage_products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_products.php') ? 'active' : ''; ?>">Manage Products</a></li>
      
      <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 15px 0;">

      <!-- Website ke sath connection -->
      <li><a href="../markets.php" target="_blank">View Live Markets &rarr;</a></li>
      <li><a href="../landingpage.php" target="_blank">Visit Landing Page &rarr;</a></li>
    </ul>
  </div>
  <div>
    <!-- Logout with correct path('../logout.php' kyun ke admin folder ke andar hai) -->
    <a href="../logout.php" class="logout-btn">Log Out</a>
  </div>
</div>