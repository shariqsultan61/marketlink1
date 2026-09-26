<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'farmer' && $_SESSION['role'] !== 'admin')) {
    die("Access denied. Aapko Farmer ya Admin hona zaroori hai.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $farmer_id = $_SESSION['user_id'];
    $market_id = !empty($_POST['market_id']) ? intval($_POST['market_id']) : NULL;
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock_quantity = intval($_POST['stock_quantity']);
    
    // Image Upload Process
    $image_name = NULL;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $image_name = time() . '_' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image_name);
    }

    $stmt = $conn->prepare("INSERT INTO products (farmer_id, market_id, title, category, price, stock_quantity, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissdis", $farmer_id, $market_id, $title, $category, $price, $stock_quantity, $image_name);

    if ($stmt->execute()) {
        echo "Product successfully add ho gaya hai!";
    } else {
        echo "Error: Product add nahi ho saka.";
    }
}

$markets = $conn->query("SELECT id, name, location FROM markets");
?>

<h2>Add New Product</h2>
<form method="POST" enctype="multipart/form-data">
    <label>Product Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Category:</label><br>
    <input type="text" name="category" required><br><br>

    <label>Price ($):</label><br>
    <input type="number" step="0.01" name="price" required><br><br>

    <label>Stock Quantity:</label><br>
    <input type="number" name="stock_quantity" required><br><br>

    <label>Select Market/Location:</label><br>
    <select name="market_id">
        <option value="">-- No Specific Market --</option>
        <?php while ($m = $markets->fetch_assoc()): ?>
            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['name']) ?> (<?= htmlspecialchars($m['location']) ?>)</option>
        <?php endwhile; ?>
    </select><br><br>

    <label>Product Image:</label><br>
    <input type="file" name="image" accept="image/*"><br><br>

    <button type="submit">Add Product</button>
</form> 