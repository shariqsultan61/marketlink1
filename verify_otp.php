<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$test_otp = isset($_SESSION['user_otp']) ? $_SESSION['user_otp'] : 'N/A';
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = trim($_POST['otp']);

    // Check OTP and Expiry from DB
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND otp = ? AND expires_at > NOW()");
    $stmt->bind_param("ss", $email, $entered_otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $message = "Invalid ya Expired OTP!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        .form-box { max-width: 400px; padding: 20px; border: 1px solid #ccc; border-radius: 8px; }
        .otp-box { background: #e9ecef; padding: 10px; margin-bottom: 15px; border-radius: 5px; color: #333; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Verify OTP</h2>

        <!-- Testing Notification for Localhost -->
        <div class="otp-box">
            <small><b>Localhost Testing Mode:</b></small><br>
            Aapka Testing OTP hai: <b style="color: red; font-size: 18px;"><?php echo $test_otp; ?></b>
        </div>

        <?php if(!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>

        <form method="POST">
            <label>Enter OTP sent to (<?php echo $email; ?>):</label><br><br>
            <input type="text" name="otp" placeholder="6-Digit OTP" required maxlength="6"><br><br>
            <button type="submit">Verify OTP</button>
        </form>
    </div>
</body>
</html>