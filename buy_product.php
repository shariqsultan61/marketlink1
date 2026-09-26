<?php
session_start();
require_once 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    die("Pehle as a Customer login karein.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);

    // Stock & Product Details Fetch
    $stmt = $conn->prepare("SELECT title, price, stock_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product || $product['stock_quantity'] < $quantity) {
        die("Stock available nahi hai ya product exist nahi karta.");
    }

    $total_price = $product['price'] * $quantity;

    // 1. Order Entry in Database
    $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, product_id, quantity, total_price, order_status) VALUES (?, ?, ?, ?, 'completed')");
    $order_stmt->bind_param("iiid", $customer_id, $product_id, $quantity, $total_price);
    
    if ($order_stmt->execute()) {
        $order_id = $conn->insert_id;

        // 2. Reduce Stock Quantity
        $update_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
        $update_stock->bind_param("ii", $quantity, $product_id);
        $update_stock->execute();

        // 3. Customer Email Fetch
        $user_stmt = $conn->prepare("SELECT fullname, email FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $customer_id);
        $user_stmt->execute();
        $customer = $user_stmt->get_result()->fetch_assoc();

        // 4. Real-time Email Sending via Gmail SMTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'your_gmail@gmail.com'; // Apni real Gmail ID yahan dalein
            $mail->Password   = 'your_app_password';    // Google App Password yahan dalein
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('your_gmail@gmail.com', 'MarketLink Store');
            $mail->addAddress($customer['email'], $customer['fullname']);

            $mail->isHTML(true);
            $mail->Subject = "Order Confirmation - Order #$order_id";
            $mail->Body    = "
                <h2>Purchase Successful!</h2>
                <p>Hi <b>{$customer['fullname']}</b>,</p>
                <p>Aapka order successfully place ho gaya hai. Details yeh hain:</p>
                <ul>
                    <li><b>Order ID:</b> #{$order_id}</li>
                    <li><b>Product:</b> {$product['title']}</li>
                    <li><b>Quantity:</b> {$quantity}</li>
                    <li><b>Total Amount:</b> \${$total_price}</li>
                </ul>
                <p>MarketLink se shopping karne ka shukriya!</p>
            ";

            $mail->send();
            echo "Purchase complete! Real-time email notification aapke Gmail (" . htmlspecialchars($customer['email']) . ") par bhej diya gaya hai.";
        } catch (Exception $e) {
            echo "Purchase successful, lekin email nahi ja saki. Error: {$mail->ErrorInfo}";
        }
    }
}
?>