<?php
require_once 'config.php';
session_start();

$siteName = 'MarketLink';$year     = date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action']) &&$input['action'] === 'cancel_order') {
        $orderId =$input['order_id'];
        $userId =$_SESSION['user_id'];
        try {
            $stmtCheck =$pdo->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
            $stmtCheck->execute([$orderId,$userId]);
            $order =$stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($order &&$order['status'] === 'In Process') {
                $stmtCancel =$pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ?");
                $stmtCancel->execute([$orderId]);
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Order cannot be cancelled after approval or processing.']);
            }
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if (isset($input['action']) &&$input['action'] === 'fetch_orders') {
        $userId =$_SESSION['user_id'];
        try {
            $stmt =$pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
            $stmt->execute([$userId]);
            $orders =$stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($orders as &$order) {
                $stmtItems =$pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                $stmtItems->execute([$order['id']]);
                $order['items'] =$stmtItems->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode(['status' => 'success', 'orders' => $orders]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }

    if (!$input || empty($input['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }

    $userId = $_SESSION['user_id'];$orderId = 'ML-' . mt_rand(100000, 999999);
    $totalAmount = 0;

    foreach ($input['items'] as $item) {$totalAmount += ($item['price'] *$item['qty']);
    }

    try {
        $pdo->beginTransaction();

        $stmtOrder =$pdo->prepare("INSERT INTO orders (user_id, order_id, total_amount, status) VALUES (?, ?, ?, 'In Process')");
        $stmtOrder->execute([$userId, $orderId,$totalAmount]);
        $dbOrderId =$pdo->lastInsertId();

        $stmtItem =$pdo->prepare("INSERT INTO order_items (order_id, product_name, stall_name, price, quantity) VALUES (?, ?, ?, ?, ?)");

        foreach ($input['items'] as$item) {
            $stmtItem->execute([$dbOrderId,
                $item['productName'],$item['stallName'],
                $item['price'],$item['qty']
            ]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'order_id' => $orderId]);
    } catch (Exception $e) {$pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

try {
    $stmtMarkets =$pdo->query("SELECT * FROM markets");
    $dbMarkets =$stmtMarkets->fetchAll(PDO::FETCH_ASSOC);

    $marketsData = [];
    foreach ($dbMarkets as$m) {
        $stmtFarmers =$pdo->prepare("SELECT * FROM farmers WHERE market_id = ?");
        $stmtFarmers->execute([$m['market_id']]);
        $dbFarmers =$stmtFarmers->fetchAll(PDO::FETCH_ASSOC);

        $farmersList = [];
        foreach ($dbFarmers as$f) {
            $stmtProducts =$pdo->prepare("SELECT name, price FROM products WHERE farmer_id = ?");
            $stmtProducts->execute([$f['id']]);
            $dbProducts =$stmtProducts->fetchAll(PDO::FETCH_ASSOC);

            $farmersList[] = [
                'stall_name'     => $f['stall_name'],
                'contact_person' => $f['contact_person'],
                'time_slot'      => $f['time_slot'] ?? '8:00 AM - 1:00 PM',
                'phone'          => $f['phone'] ?? '+92 (300) 000-0000',                 'email'          =>$f['email'] ?? '',
                'bio'            => $f['bio'] ?? '',
                'avatar'         => $f['avatar'] ?? '🌾',
                'products'       => $dbProducts
            ];
        }

        $marketsData[] = [
            'market_id'      => (int)$m['market_id'],
            'market_name'    => $m['market_name'],
            'address'        => $m['address'],
            'location'       => $m['location_area'],
            'operating_days' => $m['operating_days'],
            'timing'         => $m['timing'],
            'latitude'       => (float)$m['latitude'],
            'longitude'      => (float)$m['longitude'],
            'farmers'        => $farmersList
        ];
    }
} catch (PDOException $e) {$marketsData = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Browse Markets & Farmers — <?php echo htmlspecialchars($siteName); ?></title>
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🧺</text></svg>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,560;0,9..144,680;1,9..144,500&family=Work+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

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
  --max: 1150px;
  --card-bg: #fbf8ee;
}

*, *::before, *::after { box-sizing: border-box; }

body {
  margin: 0; background: var(--canvas); color: var(--ink);
  font-family: var(--font-body); font-size: 16px; line-height: 1.55;
  -webkit-font-smoothing: antialiased;
}

a { color: inherit; text-decoration: none; }
h1, h2, h3 { font-family: var(--font-display); margin: 0; color: var(--evergreen-2); font-weight: 680; line-height: 1.1; }

.eyebrow {
  font-family: var(--font-body); font-weight: 600; font-size: .85rem;
  color: var(--evergreen); text-transform: uppercase; letter-spacing: .05em;
}
.eyebrow::before { content: "— "; color: var(--harvest-dark); }
.wrap { max-width: var(--max); margin: 0 auto; padding: 0 24px; }

.site-header {
  position: sticky; top: 0; z-index: 1000; background: var(--canvas);
  opacity: 0.98; backdrop-filter: blur(14px); border-bottom: 1px solid var(--cream-line);
}
.site-header .nav { display: flex; justify-content: space-between; align-items: center; height: 76px; gap: 15px; }

.logo {
  display: flex; align-items: center; gap: 8px;
  font-family: var(--font-display); font-weight: 680; font-size: 1.2rem; color: var(--evergreen-2);
}

.nav-links { display: flex; gap: 18px; align-items: center; }
.nav-links a { font-size: .88rem; font-weight: 500; color: var(--ink); opacity: 0.85; transition: all 0.2s; }
.nav-links a:hover { opacity: 1; color: var(--evergreen); }
.nav-actions { display: flex; gap: 8px; align-items: center; }

.cart-btn-nav {
  background: #e3efd8; color: var(--evergreen-2); border: 1.5px solid var(--cream-line);
  padding: 7px 12px; border-radius: var(--radius); font-weight: 600; font-size: 0.85rem;
  cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s;
}
.cart-btn-nav:hover { background: var(--evergreen); color: #fff; }

.btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 6px;
  padding: 9px 16px; border-radius: var(--radius); font-weight: 600; font-size: .88rem;
  cursor: pointer; border: 1.5px solid transparent; transition: 0.2s;
}
.btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(46, 74, 44, 0.15); }
.btn-primary { background: var(--evergreen); color: #fbf8ef; }
.btn-primary:hover { background: var(--evergreen-2); }
.btn-ghost { background: transparent; border-color: var(--cream-line); color: var(--evergreen-2); }
.btn-ghost:hover { border-color: var(--evergreen); background: var(--cream-line); }

.browse-section { padding: 40px 0 80px; }
.browse-header { margin-bottom: 35px; }
.browse-header h1 { font-size: 2.5rem; margin-top: 6px; }

.top-browse-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 30px; margin-bottom: 40px; }
.filter-box {
  background: var(--card-bg); border: 1px solid var(--cream-line); padding: 28px;
  border-radius: 12px; box-shadow: 0 8px 24px rgba(32, 40, 28, 0.04);
  display: flex; flex-direction: column; justify-content: space-between;
}
.filter-group { margin-bottom: 14px; }
.filter-group label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--evergreen-2); margin-bottom: 4px; }
.filter-group select, .filter-group input {
  width: 100%; padding: 10px 12px; border: 1.5px solid var(--cream-line); border-radius: var(--radius);
  background: var(--canvas); color: var(--ink); font-size: 0.92rem; outline: none;
}
#map { width: 100%; height: 100%; min-height: 340px; border-radius: 12px; border: 1px solid var(--cream-line); }

.markets-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 22px; }
.market-card {
  background: var(--card-bg); border: 1px solid var(--cream-line); padding: 24px;
  border-radius: 12px; display: flex; flex-direction: column; justify-content: space-between; transition: 0.25s;
}
.market-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(46, 74, 44, 0.1); border-color: var(--evergreen); }
.market-badge {
  display: inline-block; background: #e3efd8; color: var(--evergreen); font-size: 0.75rem;
  font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; margin-bottom: 10px;
}
.market-card h3 { font-size: 1.25rem; margin-bottom: 6px; }
.market-details-text { font-size: 0.88rem; color: var(--ink); opacity: 0.8; margin-bottom: 16px; }

.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(32, 40, 28, 0.75);
  backdrop-filter: blur(5px); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 20px; overflow-y: auto;
}
.modal-card {
  width: 100%; max-width: 520px; background: var(--card-bg); border: 1.5px solid var(--cream-line);
  padding: 30px; border-radius: 16px; box-shadow: 0 30px 60px rgba(32, 40, 28, 0.3);
  position: relative; max-height: 85vh; overflow-y: auto; margin: auto;
}
.close-modal {
  position: absolute; top: 18px; right: 18px; background: var(--canvas); border: 1px solid var(--cream-line);
  color: var(--evergreen-2); width: 32px; height: 32px; border-radius: 50%; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
}
.stall-block { background: var(--canvas); border: 1px solid var(--cream-line); padding: 16px; border-radius: var(--radius); margin-bottom: 14px; }
.stall-header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.stall-avatar { font-size: 1.8rem; width: 44px; height: 44px; background: #e3efd8; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.product-item-row {
  display: flex; justify-content: space-between; align-items: center; background: var(--card-bg);
  padding: 8px 12px; border-radius: 6px; margin-top: 6px; border: 1px solid var(--cream-line); font-size: 0.85rem;
}
.cart-item-row {
  display: flex; justify-content: space-between; align-items: center; background: var(--canvas);
  padding: 10px 12px; border-radius: var(--radius); margin-bottom: 8px; border: 1px solid var(--cream-line);
}

.site-footer { background: #233a22; color: #cfd6c4; padding: 50px 0 25px; border-top: 1px solid rgba(255, 255, 255, 0.08); }
.footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 30px; margin-bottom: 35px; }
.footer-col-brand .footer-logo { display: flex; align-items: center; gap: 10px; font-weight: 680; font-size: 1.2rem; color: #fbf8ef; margin-bottom: 12px; }
.footer-col h4 { color: #fbf8ef; font-size: 1rem; margin-bottom: 14px; }
.footer-col ul { list-style: none; padding: 0; margin: 0; }
.footer-col ul li { margin-bottom: 8px; }
.footer-col ul li a { font-size: 0.88rem; opacity: 0.8; }
.footer-bottom { display: flex; justify-content: space-between; align-items: center; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.82rem; opacity: 0.75; }
</style>
</head>
<body>

<header class="site-header">
  <nav class="nav wrap">
    <a href="landingpage.php" class="logo">
      <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M4 10 L12 4 L20 10 V20 H4 Z" stroke="#2e4a2c" stroke-width="1.6" fill="#e0a52c"/><path d="M9 20 V14 H15 V20" stroke="#2e4a2c" stroke-width="1.6" fill="none"/></svg>
      <?php echo htmlspecialchars($siteName); ?>
    </a>
    
    <div class="nav-links" id="navLinks">
      <a href="landingpage.php#features">Features</a>
      <a href="markets.php" style="color:var(--evergreen); font-weight:600;">Markets</a>
      <a href="landingpage.php#faq">FAQ</a>
    </div>

    <div class="nav-actions">
      <button class="cart-btn-nav" onclick="openCartModal()">🛒 Cart (<span id="cartCount">0</span>)</button>
      <button class="cart-btn-nav" id="myOrdersBtn" style="display: none; background: #eef4ed; color: var(--evergreen-2);" onclick="openOrdersModal()">📦 My Orders</button>
      
      <?php if (isset($_SESSION['user_id'])): ?>
          <a href="profile.php" class="btn btn-ghost" style="padding: 6px 10px;">👤 <span><?php echo htmlspecialchars($_SESSION['fullname']); ?></span></a>
          <a href="logout.php" style="color: var(--clay); font-weight: 600; font-size: 0.85rem;">Logout</a>
      <?php else: ?>
          <a href="login.php" class="btn btn-ghost">Log in</a>
          <a href="join.php" class="btn btn-primary">Create Account</a>
      <?php endif; ?>
    </div>
  </nav>
</header>

<main class="wrap browse-section">
  <div class="browse-header">
    <p class="eyebrow">Karachi Farmers Markets Finder</p>
    <h1>Browse Markets & Local Growers</h1>
    <p style="color:var(--ink); opacity:0.8; margin-top:4px;">Filter live Karachi markets instantly by area, add fresh produce to your cart, and place pre-orders.</p>
  </div>

  <div class="top-browse-grid">
    <div class="filter-box">
      <div>
        <h3 style="margin-bottom: 14px;">Real-Time Filters</h3>
        <div class="filter-group">
          <label for="searchQuery">Search Market or Farmer</label>
          <input type="text" id="searchQuery" placeholder="e.g. Clifton, Farhan...">
        </div>
        <div class="filter-group">
          <label for="locationFilter">Karachi Area / Location</label>
          <select id="locationFilter">
            <option value="all">All Karachi Areas</option>
            <option value="Clifton">Clifton</option>
            <option value="Defence (DHA)">Defence (DHA)</option>
            <option value="Gulshan-e-Iqbal">Gulshan-e-Iqbal</option>
          </select>
        </div>
        <div class="filter-group">
          <label for="dayFilter">Operating Day</label>
          <select id="dayFilter">
            <option value="all">All Days</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
            <option value="Wednesday">Wednesday</option>
          </select>
        </div>
      </div>
    </div>
    <div><div id="map"></div></div>
  </div>

  <h2 style="margin-bottom: 20px;">Active Karachi Markets (<span id="marketCount">0</span>)</h2>
  <div class="markets-grid" id="marketsGridContainer"></div>
</main>

<div id="stallsModal" class="modal-overlay" onclick="handleOutsideClick(event, 'stallsModal')">
  <div class="modal-card">
    <button type="button" class="close-modal" onclick="closeStallsModal()">&times;</button>
    <h2 id="modalMarketTitle" style="font-size: 1.4rem; margin-top: 4px;">Market Name</h2>
    <p id="modalMarketAddress" style="font-size: 0.85rem; opacity: 0.75; margin-bottom: 16px;"></p>
    <div id="modalStallsContainer"></div>
  </div>
</div>

<div id="cartModal" class="modal-overlay" onclick="handleOutsideClick(event, 'cartModal')">
  <div class="modal-card">
    <button type="button" class="close-modal" onclick="closeCartModal()">&times;</button>
    <h2>Shopping Cart</h2>
    <div id="cartItemsContainer" style="max-height: 260px; overflow-y: auto; margin: 16px 0;"></div>
    <div style="font-weight: 700; margin-bottom: 16px; display: flex; justify-content: space-between;">
      <span>Total Amount:</span><span id="cartTotalPrice" style="color: var(--evergreen);">Rs. 0</span>
    </div>
    <button type="button" class="btn btn-primary" style="width: 100%;" onclick="checkoutCart()">Proceed to Checkout</button>
  </div>
</div>

<div id="ordersModal" class="modal-overlay" onclick="handleOutsideClick(event, 'ordersModal')">
  <div class="modal-card" style="max-width: 580px;">
    <button type="button" class="close-modal" onclick="closeOrdersModal()">&times;</button>
    <h2>Your Orders History</h2>
    <div id="userOrdersContainer" style="max-height: 340px; overflow-y: auto; margin-top: 16px;"></div>
  </div>
</div>

<div id="orderSuccessModal" class="modal-overlay" onclick="handleOutsideClick(event, 'orderSuccessModal')">
  <div class="modal-card" style="text-align: center;">
    <button type="button" class="close-modal" onclick="closeOrderSuccessModal()">&times;</button>
    <h2>Order Placed Successfully!</h2>
    <p id="orderPickupText" style="font-size: 0.9rem; opacity: 0.85; margin: 15px 0;"></p>
    <a id="orderDirectionsBtn" href="#" target="_blank" class="btn btn-primary" style="width: 100%;">Get Directions</a>
  </div>
</div>

<footer class="site-footer">
  <div class="wrap">
    <div class="footer-bottom">
      <div>© 2026 MarketLink. Payment is always settled in person at pickup.</div>
    </div>
  </div>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
  const allMarkets = <?php echo json_encode($marketsData); ?>;
  let cart = [];
  const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

  if (isLoggedIn && document.getElementById('myOrdersBtn')) {
    document.getElementById('myOrdersBtn').style.display = 'inline-flex';
  }

  const map = L.map('map').setView([24.8607, 67.0011], 12);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
  let mapMarkersLayer = L.layerGroup().addTo(map);
  setTimeout(() => { map.invalidateSize(); }, 200);

  function filterAndRenderMarkets() {
    const searchQuery = document.getElementById('searchQuery').value.toLowerCase().trim();
    const selectedLocation = document.getElementById('locationFilter').value;
    const selectedDay = document.getElementById('dayFilter').value;

    const filtered = allMarkets.filter(market => {
      const matchLoc = (selectedLocation === 'all' || market.location.toLowerCase() === selectedLocation.toLowerCase());
      const matchDay = (selectedDay === 'all' || market.operating_days.toLowerCase() === selectedDay.toLowerCase());
      let matchSearch = searchQuery === '' || market.market_name.toLowerCase().includes(searchQuery) || market.farmers.some(f => f.stall_name.toLowerCase().includes(searchQuery));
      return matchLoc && matchDay && matchSearch;
    });

    renderMarkets(filtered);
    renderMapMarkers(filtered);
  }

  function renderMarkets(markets) {
    const container = document.getElementById('marketsGridContainer');
    document.getElementById('marketCount').textContent = markets.length;
    container.innerHTML = '';
    markets.forEach(m => {
      container.innerHTML += `
        <div class="market-card">
          <div>
            <span class="market-badge">${m.operating_days}</span>
            <h3>${m.market_name}</h3>
            <p style="font-size: 0.85rem; opacity: 0.8;">📍 ${m.location} — ${m.address}</p>
          </div>
          <button type="button" class="btn btn-primary" style="margin-top:14px;" onclick="openStallsModal(${m.market_id})">View Stalls & Products</button>
        </div>`;
    });
  }

  function renderMapMarkers(markets) {
    mapMarkersLayer.clearLayers();
    markets.forEach(m => {
      if (m.latitude && m.longitude) {
        L.marker([m.latitude, m.longitude]).addTo(mapMarkersLayer).bindPopup(`<b>${m.market_name}</b>`);
      }
    });
  }

  function openStallsModal(marketId) {
    const market = allMarkets.find(m => m.market_id === marketId);
    if (!market) return;

    document.getElementById('modalMarketTitle').textContent = market.market_name;
    document.getElementById('modalMarketAddress').textContent = market.address;

    const container = document.getElementById('modalStallsContainer');
    container.innerHTML = '';

    market.farmers.forEach(farmer => {
      let productRows = '';
      farmer.products.forEach(p => {
        productRows += `
          <div class="product-item-row">
            <span>${p.name} — <strong>Rs. ${p.price}</strong></span>
            <button type="button" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.78rem;" onclick="addToCart('${encodeURIComponent(farmer.stall_name)}', '${encodeURIComponent(p.name)}', ${p.price}, '${encodeURIComponent(market.market_name)}', '${encodeURIComponent(market.location)}', '${encodeURIComponent(market.address)}', ${market.latitude}, ${market.longitude})">+ Add</button>
          </div>`;
      });

      container.innerHTML += `
        <div class="stall-block">
          <h4 style="margin:0 0 4px 0; color:var(--evergreen-2);">${farmer.stall_name}</h4>
          <p style="font-size:0.8rem; opacity:0.7; margin:0 0 8px 0;">Grower: ${farmer.contact_person}</p>
          ${productRows}
        </div>`;
    });

    document.getElementById('stallsModal').style.display = 'flex';
  }

  function closeStallsModal() { document.getElementById('stallsModal').style.display = 'none'; }
  
  function openCartModal() {
    closeStallsModal();
    const container = document.getElementById('cartItemsContainer');
    container.innerHTML = '';

    if (cart.length === 0) {
      container.innerHTML = '<p style="text-align: center; opacity: 0.7;">Your cart is empty.</p>';
      document.getElementById('cartTotalPrice').textContent = 'Rs. 0';
      document.getElementById('cartModal').style.display = 'flex';
      return;
    }

    let totalPrice = 0;
    cart.forEach((item, index) => {
      let itemTotal = item.price * item.qty;
      totalPrice += itemTotal;
      container.innerHTML += `
        <div class="cart-item-row">
          <div>
            <strong>${item.productName}</strong><br>
            <small style="opacity:0.7;">Stall: ${item.stallName}</small>
          </div>
          <div style="text-align: right;">
            <span>Rs. ${itemTotal}</span><br>
            <button class="btn btn-ghost" style="padding:2px 6px;" onclick="changeQty(${index}, -1)">-</button>
            <span>${item.qty}</span>
            <button class="btn btn-ghost" style="padding:2px 6px;" onclick="changeQty(${index}, 1)">+</button>
          </div>
        </div>`;
    });

    document.getElementById('cartTotalPrice').textContent = 'Rs. ' + totalPrice;
    document.getElementById('cartModal').style.display = 'flex';
  }

  function closeCartModal() { document.getElementById('cartModal').style.display = 'none'; }

  function addToCart(encStall, encProduct, price, encMarket, encLoc, encAddr, lat, lng) {
    const stallName = decodeURIComponent(encStall);
    const productName = decodeURIComponent(encProduct);
    const marketName = decodeURIComponent(encMarket);
    const marketLocation = decodeURIComponent(encLoc);
    const marketAddress = decodeURIComponent(encAddr);

    const existing = cart.find(item => item.stallName === stallName && item.productName === productName);
    if (existing) {
      existing.qty += 1;
    } else {
      cart.push({ stallName, productName, price, marketName, marketLocation, marketAddress, lat, lng, qty: 1 });
    }
    document.getElementById('cartCount').textContent = cart.reduce((sum, item) => sum + item.qty, 0);
    alert(productName + ' added to cart successfully!');
  }

  function changeQty(index, delta) {
    cart[index].qty += delta;
    if (cart[index].qty <= 0) cart.splice(index, 1);
    document.getElementById('cartCount').textContent = cart.reduce((sum, item) => sum + item.qty, 0);
    openCartModal();
  }

  function checkoutCart() {
    if (cart.length === 0) return;
    if (!isLoggedIn) {
      alert('Please log in to your account before checking out.');
      window.location.href = 'login.php';
      return;
    }

    fetch(window.location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items: cart })
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        const primaryItem = cart[0];
        closeCartModal();
        document.getElementById('orderPickupText').innerHTML = `Order ID: <strong>${data.order_id}</strong><br>Pickup Location: <strong>${primaryItem.marketLocation}</strong> (${primaryItem.marketAddress})`;
        document.getElementById('orderDirectionsBtn').href = `https://www.google.com/maps/dir/?api=1&destination=${primaryItem.lat},${primaryItem.lng}`;
        document.getElementById('orderSuccessModal').style.display = 'flex';
        cart = [];
        document.getElementById('cartCount').textContent = '0';
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('An error occurred while placing the order.');
    });
  }

  function openOrdersModal() {
    const container = document.getElementById('userOrdersContainer');
    container.innerHTML = '<p style="text-align:center;">Loading orders...</p>';
    document.getElementById('ordersModal').style.display = 'flex';

    fetch(window.location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'fetch_orders' })
    })
    .then(response => response.json())
    .then(data => {
      container.innerHTML = '';
      if (data.status === 'success' && data.orders.length > 0) {
        data.orders.forEach(order => {
          let itemsHtml = '';
          order.items.forEach(i => {
            itemsHtml += `<div style="font-size:0.85rem; opacity:0.85;">• ${i.product_name} (x${i.quantity}) — Rs. ${i.price * i.quantity}</div>`;
          });

          let statusDisplay = order.status;
          if (order.status === 'Ready') {
            statusDisplay = 'Ready to Pickup';
          }

          let cancelBtnHtml = '';
          if (order.status === 'In Process') {
            cancelBtnHtml = `<button class="btn btn-ghost" style="padding:4px 10px; color:var(--clay); border-color:var(--clay); font-size:0.75rem;" onclick="cancelOrder(${order.id})">Cancel Order</button>`;
          }

          container.innerHTML += `
            <div class="stall-block">
              <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <strong>${order.order_id}</strong>
                <span style="background:#e3efd8; color:var(--evergreen); padding:3px 8px; border-radius:12px; font-size:0.75rem; font-weight:700;">${statusDisplay}</span>
              </div>
              <div style="margin-bottom:8px;">${itemsHtml}</div>
              <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem;">
                <span>Total: <strong>Rs. ${order.total_amount}</strong></span>
                ${cancelBtnHtml}
              </div>
            </div>`;
        });
      } else {
        container.innerHTML = '<p style="text-align:center; opacity:0.7;">No orders found.</p>';
      }
    })
    .catch(err => {
      container.innerHTML = '<p style="text-align:center; color:red;">Failed to load orders.</p>';
    });
  }

  function cancelOrder(orderId) {
    if (!confirm('Kya aap waqai is order ko cancel karna chahte hain?')) return;

    fetch(window.location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'cancel_order', order_id: orderId })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert('Order kamiyabi ke sath cancel ho gaya hai.');
        openOrdersModal();
      } else {
        alert(data.message || 'Order cancel nahi ho saka.');
      }
    });
  }

  function closeOrdersModal() { document.getElementById('ordersModal').style.display = 'none'; }
  function closeOrderSuccessModal() { document.getElementById('orderSuccessModal').style.display = 'none'; }

  function handleOutsideClick(event, id) {
    if (event.target.id === id) document.getElementById(id).style.display = 'none';
  }

  document.getElementById('searchQuery').addEventListener('input', filterAndRenderMarkets);
  document.getElementById('locationFilter').addEventListener('change', filterAndRenderMarkets);
  document.getElementById('dayFilter').addEventListener('change', filterAndRenderMarkets);
  filterAndRenderMarkets();
</script>
</body>
</html>