<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['reset_email']) || !isset($_SESSION['otp_verified'])) {
    header("Location: forgot-password.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['reset_email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $message = "Passwords match nahi kar rahe!";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);

        if ($stmt->execute()) {
            $del_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $del_stmt->bind_param("s", $email);
            $del_stmt->execute();

            unset($_SESSION['reset_email']);
            unset($_SESSION['otp_verified']);

            header("Location: login.php?msg=password_updated");
            exit();
        } else {
            $message = "Error: Password update nahi ho saka.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Reset Password</title></head>
<body>
    <h2>Set New Password</h2>
    <?php if(!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
    <form method="POST">
        <label>New Password:</label><br>
        <input type="password" name="new_password" required><br><br>

        <label>Confirm New Password:</label><br>
        <input type="password" name="confirm_password" required><br><br>

        <button type="submit">Update Password</button>
    </form>
</body>
</html>