<?php
session_start();
require_once 'config.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
}

// Variable name mismatch handling ($conn, $con, $db)
if (!isset($conn)) {
    if (isset($con)) { $conn = $con; }
    elseif (isset($db)) { $conn = $db; }
}

if (!$conn) {
    die("Database Connection Fail Ho Gaya Hai. Check config.php file.");
}

// Auto-Fix Table Schema if missing or outdated
$table_check = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
)";
$conn->query($table_check);

// Check & Add 'expires_at' column if missing in existing table
$check_expires = $conn->query("SHOW COLUMNS FROM password_resets LIKE 'expires_at'");
if ($check_expires && $check_expires->num_rows == 0) {
    $conn->query("ALTER TABLE password_resets ADD COLUMN expires_at DATETIME NOT NULL AFTER otp");
}

// Check & Add 'otp' column if missing in existing table
$check_otp = $conn->query("SHOW COLUMNS FROM password_resets LIKE 'otp'");
if ($check_otp && $check_otp->num_rows == 0) {
    $conn->query("ALTER TABLE password_resets ADD COLUMN otp VARCHAR(6) NOT NULL AFTER email");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    // Check if Email exists in DB
    $stmt = $conn->prepare("SELECT id, fullname FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        // Clear Old Pending OTPs for this Email
        $del_old = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $del_old->bind_param("s", $email);
        $del_old->execute();

        // Insert New OTP with Expiry Time
        $stmt_otp = $conn->prepare("INSERT INTO password_resets (email, otp, expires_at) VALUES (?, ?, ?)");
        $stmt_otp->bind_param("sss", $email, $otp, $expires_at);
        $stmt_otp->execute();

        $_SESSION['reset_email'] = $email;
        $_SESSION['user_otp'] = $otp; 

        // PHPMailer Real-Time Send
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your_gmail@gmail.com'; // Apni Gmail ID yahan dalein
                $mail->Password   = 'your_app_password';    // Google App Password yahan dalein
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom('your_gmail@gmail.com', 'MarketLink Support');
                $mail->addAddress($email, $user['fullname']);

                $mail->isHTML(true);
                $mail->Subject = 'MarketLink - Password Reset OTP';
                $mail->Body    = "
                    <h3>Password Reset OTP</h3>
                    <p>Hi <b>{$user['fullname']}</b>,</p>
                    <p>Aapka Password Reset OTP: <b style='font-size: 20px; color: #007bff;'>$otp</b></p>
                    <p>Yeh code 10 minute tak valid hai.</p>
                ";

                $mail->send();
            } catch (Exception $e) {
                // Silently hold session for local environment testing
            }
        }

        header("Location: verify_otp.php");
        exit();
    } else {
        $message = "Yeh email system me registered nahi hai.";
    }
}

$mail->Username   = 'your_gmail@gmail.com'; // Aapka Gmail address[cite: 7]
$mail->Password   = 'abcd efgh ijkl mnop';   // 16-digit Google App Password[cite: 7]
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .form-box { max-width: 400px; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        input[type="email"] { width: 95%; padding: 8px; margin-top: 5px; }
        button { background-color: #007bff; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Forgot Password</h2>
        <?php if(!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
        <form method="POST">
            <label>Enter Registered Email:</label><br>
            <input type="email" name="email" required><br>
            <button type="submit">Send OTP</button>
        </form>
    </div>
</body>
</html>